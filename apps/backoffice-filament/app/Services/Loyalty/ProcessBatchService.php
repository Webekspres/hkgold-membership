<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Data\Loyalty\ProcessBatchResult;
use App\Data\Loyalty\ProcessBatchSummary;
use App\Enums\InjectionStatus;
use App\Exceptions\Loyalty\ProcessBatchException;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Member;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use App\Models\PointMutation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessBatchService
{
    public function __construct(
        private readonly PointCalculationService $pointCalculation,
    ) {}

    public function assertBatchCanProcess(PointInjectionBatch $batch, bool $ignoreProcessingFlag = false): void
    {
        $batch->loadCount('details');

        if ($batch->resolved) {
            throw ProcessBatchException::batchAlreadyResolved();
        }

        if (! $ignoreProcessingFlag && $batch->processing_started_at !== null) {
            throw ProcessBatchException::batchAlreadyProcessing();
        }

        if ($batch->import_started_at !== null && (int) $batch->details_count < $batch->total_rows) {
            throw ProcessBatchException::importIncomplete();
        }

        if ($batch->total_rows === 0) {
            throw ProcessBatchException::batchNotReady('Tidak ada baris data untuk diproses.');
        }

        if ($batch->failed_rows > 0) {
            throw ProcessBatchException::batchNotReady(
                'Masih ada '.$batch->failed_rows.' baris yang gagal. Perbaiki dulu sebelum memproses.',
            );
        }

        if ($batch->successful_rows !== $batch->total_rows) {
            throw ProcessBatchException::batchNotReady('Belum semua baris selesai divalidasi.');
        }

        if ((int) $batch->details_count < $batch->total_rows) {
            throw ProcessBatchException::importIncomplete();
        }

        $invalidCount = $batch->details()
            ->whereNotIn('status', [InjectionStatus::Validated, InjectionStatus::Success])
            ->count();

        if ($invalidCount > 0) {
            throw ProcessBatchException::batchNotReady(
                'Masih ada baris yang belum selesai divalidasi.',
            );
        }

        $validatedCount = $batch->details()
            ->where('status', InjectionStatus::Validated)
            ->count();

        if ($validatedCount !== $batch->total_rows) {
            throw ProcessBatchException::batchNotReady('Tidak semua baris siap diproses.');
        }
    }

    public function buildSummary(PointInjectionBatch $batch): ProcessBatchSummary
    {
        $query = $batch->details()->where('status', InjectionStatus::Validated);

        return new ProcessBatchSummary(
            uniqueMembers: (int) $query->clone()->distinct('raw_member_number')->count('raw_member_number'),
            totalRows: (int) $query->clone()->count(),
            totalPoints: (int) $query->clone()->sum('calculated_points'),
            totalNominal: (string) $query->clone()->sum('purchase_nominal'),
        );
    }

    public function process(PointInjectionBatch $batch, User $actor, string $ipAddress): ProcessBatchResult
    {
        return DB::transaction(function () use ($batch, $actor, $ipAddress): ProcessBatchResult {
            $lockedBatch = PointInjectionBatch::query()
                ->whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedBatch->loadCount('details');
            $this->assertBatchCanProcess($lockedBatch, ignoreProcessingFlag: true);

            $details = PointInjectionDetail::query()
                ->where('batch_id', $lockedBatch->id)
                ->where('status', InjectionStatus::Validated)
                ->orderBy('row_number')
                ->get();

            $previousTotalPointsInjected = (int) $lockedBatch->total_points_injected;
            $previousResolved = (bool) $lockedBatch->resolved;

            $totalPointsInjected = 0;
            $processedMemberNumbers = [];

            foreach ($details as $detail) {
                if ($detail->status !== InjectionStatus::Validated) {
                    throw ProcessBatchException::detailNotValidated($detail->row_number);
                }

                $member = Member::query()
                    ->where('member_number', $detail->raw_member_number)
                    ->lockForUpdate()
                    ->first();

                if ($member === null) {
                    throw ProcessBatchException::memberNotFound($detail->raw_member_number);
                }

                if ($member->is_suspended) {
                    throw ProcessBatchException::memberSuspended($detail->raw_member_number);
                }

                $branchId = $this->resolveBranchId($detail->raw_branch_code);
                $transactionTypeId = $detail->transaction_type_id;

                if ($detail->receipt_number !== null && $transactionTypeId !== null) {
                    $this->assertReceiptIsUnique($detail->receipt_number, $transactionTypeId);
                }

                $pointsIssued = (int) $detail->calculated_points;
                $previousBalance = (int) $member->point_balance;
                $newBalance = $previousBalance + $pointsIssued;

                if ($pointsIssued > 0) {
                    $newHighest = max((int) $member->highest_point, $newBalance);
                    $newTier = $this->pointCalculation->resolveEligibleTierUpgrade(
                        $member->current_tier,
                        $newBalance,
                    );

                    $member->update([
                        'point_balance' => $newBalance,
                        'highest_point' => $newHighest,
                        'current_tier' => $newTier,
                        'last_activity_at' => $detail->transaction_date,
                    ]);
                }

                PointMutation::query()->create([
                    'member_id' => $member->id,
                    'branch_id' => $branchId,
                    'source_id' => $lockedBatch->id,
                    'receipt_number' => $detail->receipt_number,
                    'transaction_type_id' => $transactionTypeId,
                    'purchase_nominal' => $detail->purchase_nominal,
                    'points_issued' => $pointsIssued,
                    'points_redeemed' => 0,
                    'balance_snapshot' => $newBalance,
                    'transaction_date' => $detail->transaction_date,
                    'uploaded_at' => now(),
                ]);

                $detail->update([
                    'status' => InjectionStatus::Success,
                    'processed_at' => now(),
                ]);

                $totalPointsInjected += $pointsIssued;
                $processedMemberNumbers[$detail->raw_member_number] = true;
            }

            $lockedBatch->update([
                'total_points_injected' => $totalPointsInjected,
                'resolved' => true,
                'processing_started_at' => null,
            ]);

            ActivityLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'bulk_point_injection',
                'description' => 'Proses injeksi poin massal batch',
                'auditable_type' => 'PointInjectionBatch',
                'auditable_id' => $lockedBatch->id,
                'before_json' => [
                    'resolved' => $previousResolved,
                    'total_points_injected' => $previousTotalPointsInjected,
                ],
                'after_json' => [
                    'resolved' => true,
                    'total_points_injected' => $totalPointsInjected,
                    'rows_processed' => $details->count(),
                ],
                'ip_address' => $ipAddress,
                'created_at' => now(),
            ]);

            return new ProcessBatchResult(
                batchId: $lockedBatch->id,
                rowsProcessed: $details->count(),
                totalPointsInjected: $totalPointsInjected,
                uniqueMembers: count($processedMemberNumbers),
            );
        });
    }

    private function resolveBranchId(?string $rawBranchCode): ?int
    {
        $code = trim((string) $rawBranchCode);

        if ($code === '') {
            return null;
        }

        $branchId = Branch::query()->where('branch_code', $code)->value('id');

        if ($branchId === null) {
            throw ProcessBatchException::branchNotFound($code);
        }

        return (int) $branchId;
    }

    private function assertReceiptIsUnique(string $receiptNumber, int $transactionTypeId): void
    {
        $exists = PointMutation::query()
            ->where('receipt_number', $receiptNumber)
            ->where('transaction_type_id', $transactionTypeId)
            ->exists();

        if ($exists) {
            throw ProcessBatchException::duplicateReceipt($receiptNumber);
        }
    }
}
