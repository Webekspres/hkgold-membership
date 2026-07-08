<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Data\Loyalty\ManualPointInjectionData;
use App\Data\Loyalty\ManualPointInjectionResult;
use App\Enums\ActivityLogAction;
use App\Exceptions\Loyalty\ManualPointInjectionException;
use App\Models\Branch;
use App\Models\Member;
use App\Models\PointMutation;
use App\Models\TransactionType;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ManualPointInjectionService
{
    public function __construct(
        private readonly PointCalculationService $pointCalculation,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function inject(ManualPointInjectionData $data, User $actor, string $ipAddress): ManualPointInjectionResult
    {
        $this->validatePayload($data);

        return DB::transaction(function () use ($data, $actor, $ipAddress): ManualPointInjectionResult {
            $member = Member::query()
                ->with('user')
                ->whereKey($data->memberId)
                ->lockForUpdate()
                ->first();

            if ($member === null) {
                throw ManualPointInjectionException::memberNotFound();
            }

            if ($member->is_suspended) {
                throw ManualPointInjectionException::memberSuspended();
            }

            $transactionType = TransactionType::query()->find($data->transactionTypeId);

            if ($transactionType === null) {
                throw ManualPointInjectionException::transactionTypeNotFound();
            }

            if ($data->branchId !== null && ! Branch::query()->whereKey($data->branchId)->exists()) {
                throw ManualPointInjectionException::branchNotFound();
            }

            $this->assertReceiptIsUnique($data->receiptNumber, $data->transactionTypeId);

            $conversionRule = $this->pointCalculation->resolveConversionRule($member, $transactionType);
            $conversionNominal = (string) $conversionRule->conversion_nominal;

            if (bccomp($data->purchaseNominal, $conversionNominal, 2) < 0) {
                throw ManualPointInjectionException::nominalBelowConversionMinimum($conversionNominal);
            }

            $pointsIssued = $this->pointCalculation->calculateIssuedPoints($data->purchaseNominal, $conversionNominal);

            if ($pointsIssued < 1) {
                throw ManualPointInjectionException::nominalBelowConversionMinimum($conversionNominal);
            }

            $previousBalance = (int) $member->point_balance;
            $previousTier = $member->current_tier;
            $previousHighest = (int) $member->highest_point;
            $previousLastActivity = $member->last_activity_at;

            $newBalance = $previousBalance + $pointsIssued;
            $newHighest = max($previousHighest, $newBalance);
            $newTier = $this->pointCalculation->resolveEligibleTierUpgrade($previousTier, $newBalance);

            $mutation = PointMutation::query()->create([
                'member_id' => $member->id,
                'branch_id' => $data->branchId,
                'receipt_number' => $data->receiptNumber,
                'transaction_type_id' => $transactionType->id,
                'purchase_nominal' => $data->purchaseNominal,
                'points_issued' => $pointsIssued,
                'points_redeemed' => 0,
                'balance_snapshot' => $newBalance,
                'transaction_date' => $data->transactionDate,
                'uploaded_at' => now(),
            ]);

            $member->update([
                'point_balance' => $newBalance,
                'highest_point' => $newHighest,
                'current_tier' => $newTier,
                'last_activity_at' => $data->transactionDate,
            ]);

            $this->activityLogger->log(
                action: ActivityLogAction::ManualPointInjection,
                description: 'Suntik poin manual oleh staff',
                auditable: $mutation,
                ipAddress: $ipAddress,
                before: [
                    'point_balance' => $previousBalance,
                    'highest_point' => $previousHighest,
                    'current_tier' => $previousTier->value,
                    'last_activity_at' => $previousLastActivity?->toIso8601String(),
                ],
                after: [
                    'point_balance' => $newBalance,
                    'highest_point' => $newHighest,
                    'current_tier' => $newTier->value,
                    'last_activity_at' => $data->transactionDate->toIso8601String(),
                ],
                actor: $actor,
            );

            return new ManualPointInjectionResult(
                mutationId: $mutation->id,
                memberId: $member->id,
                memberNumber: $member->member_number,
                memberName: (string) $member->user?->full_name,
                branchId: $data->branchId,
                transactionTypeId: $transactionType->id,
                transactionType: $transactionType->display_name,
                purchaseNominal: $data->purchaseNominal,
                pointsIssued: $pointsIssued,
                previousBalance: $previousBalance,
                newBalance: $newBalance,
                balanceSnapshot: $newBalance,
                previousTier: $previousTier,
                newTier: $newTier,
                tierUpgraded: $newTier !== $previousTier,
                receiptNumber: $data->receiptNumber,
                transactionDate: $data->transactionDate,
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(ManualPointInjectionData $data): array
    {
        $this->validatePayload($data);

        $member = Member::query()->with('user')->find($data->memberId);

        if ($member === null) {
            throw ManualPointInjectionException::memberNotFound();
        }

        if ($member->is_suspended) {
            throw ManualPointInjectionException::memberSuspended();
        }

        $transactionType = TransactionType::query()->find($data->transactionTypeId);

        if ($transactionType === null) {
            throw ManualPointInjectionException::transactionTypeNotFound();
        }

        if ($data->branchId !== null && ! Branch::query()->whereKey($data->branchId)->exists()) {
            throw ManualPointInjectionException::branchNotFound();
        }

        $this->assertReceiptIsUnique($data->receiptNumber, $data->transactionTypeId);

        $calculation = $this->pointCalculation->preview($member, $transactionType, $data->purchaseNominal);
        $conversionNominal = $calculation['conversion_nominal'];

        if (bccomp($data->purchaseNominal, $conversionNominal, 2) < 0) {
            throw ManualPointInjectionException::nominalBelowConversionMinimum($conversionNominal);
        }

        $pointsIssued = $calculation['points_issued'];

        if ($pointsIssued < 1) {
            throw ManualPointInjectionException::nominalBelowConversionMinimum($conversionNominal);
        }

        $newBalance = (int) $member->point_balance + $pointsIssued;
        $newTier = $this->pointCalculation->resolveEligibleTierUpgrade($member->current_tier, $newBalance);
        $branchName = $data->branchId !== null
            ? Branch::query()->whereKey($data->branchId)->value('name')
            : null;

        return [
            'member_name' => (string) $member->user?->full_name,
            'member_number' => $member->member_number,
            'branch_name' => $branchName,
            'transaction_type' => $transactionType->display_name,
            'purchase_nominal' => $data->purchaseNominal,
            'points_issued' => $pointsIssued,
            'previous_balance' => (int) $member->point_balance,
            'new_balance' => $newBalance,
            'previous_tier' => $member->current_tier->value,
            'new_tier' => $newTier->value,
            'tier_upgraded' => $newTier !== $member->current_tier,
            'receipt_number' => $data->receiptNumber,
            'transaction_date' => $data->transactionDate->format('d/m/Y'),
        ];
    }

    private function validatePayload(ManualPointInjectionData $data): void
    {
        if (bccomp($data->purchaseNominal, '1.00', 2) < 0) {
            throw ManualPointInjectionException::invalidPurchaseNominal();
        }

        if ($data->transactionDate->copy()->startOfDay()->gt(Carbon::today())) {
            throw ManualPointInjectionException::invalidTransactionDate();
        }
    }

    private function assertReceiptIsUnique(?string $receiptNumber, int $transactionTypeId): void
    {
        if ($receiptNumber === null) {
            return;
        }

        $exists = PointMutation::query()
            ->where('receipt_number', $receiptNumber)
            ->where('transaction_type_id', $transactionTypeId)
            ->exists();

        if ($exists) {
            throw ManualPointInjectionException::duplicateReceipt($receiptNumber, $transactionTypeId);
        }
    }
}
