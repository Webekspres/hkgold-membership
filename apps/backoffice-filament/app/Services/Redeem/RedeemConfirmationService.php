<?php

declare(strict_types=1);

namespace App\Services\Redeem;

use App\Data\Redeem\RedeemConfirmationResult;
use App\Enums\ActivityLogAction;
use App\Enums\NotificationPlatform;
use App\Enums\RedeemStatus;
use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Models\BranchRewardStock;
use App\Models\Member;
use App\Models\PointMutation;
use App\Models\RedeemInvoice;
use App\Models\RedeemToken;
use App\Models\TransactionType;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogger;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RedeemConfirmationService
{
    public function __construct(
        private readonly FonnteOtpClient $otpClient,
        private readonly ActivityLogger $activityLogger,
        private readonly NotificationService $notificationService,
    ) {}

    public function confirm(
        string $tokenCode,
        string $otp,
        User $actor,
        string $ipAddress,
    ): RedeemConfirmationResult {
        $actor->loadMissing('staff');

        if ($actor->staff === null) {
            throw RedeemConfirmationException::staffRequired();
        }

        return DB::transaction(function () use ($tokenCode, $otp, $actor, $ipAddress): RedeemConfirmationResult {
            $token = RedeemToken::query()
                ->with(['member.user', 'reward', 'branch'])
                ->where('token_code', $tokenCode)
                ->lockForUpdate()
                ->first();

            if ($token === null) {
                throw RedeemConfirmationException::tokenNotFound();
            }

            if ($token->is_used) {
                throw RedeemConfirmationException::tokenAlreadyUsed();
            }

            if ($token->expired_at->isPast()) {
                throw RedeemConfirmationException::tokenExpired();
            }

            $this->assertBranchAllowed($actor, $token->branch_id);

            $member = $token->member;
            if ($member === null) {
                throw RedeemConfirmationException::tokenNotFound();
            }

            $this->otpClient->verify(
                (string) $member->phone_number,
                $tokenCode,
                $otp,
            );

            $stock = BranchRewardStock::query()
                ->where('reward_id', $token->reward_id)
                ->where('branch_id', $token->branch_id)
                ->lockForUpdate()
                ->first();

            if ($stock === null || $stock->actual_stock < 1 || $stock->held_stock < 1) {
                throw RedeemConfirmationException::stockInconsistent();
            }

            $previousActual = (int) $stock->actual_stock;
            $previousHeld = (int) $stock->held_stock;

            $token->update(['is_used' => true]);

            $stock->decrement('actual_stock');
            $stock->decrement('held_stock');
            $stock->refresh();

            $invoiceNumber = $this->generateInvoiceNumber((string) $token->branch?->branch_code);
            $invoice = RedeemInvoice::query()->create([
                'invoice_number' => $invoiceNumber,
                'member_id' => $token->member_id,
                'staff_id' => $actor->staff->id,
                'branch_id' => $token->branch_id,
                'reward_id' => $token->reward_id,
                'points_redeemed' => $token->held_points,
                'status' => RedeemStatus::Completed,
            ]);

            $transactionType = TransactionType::query()
                ->where('type_key', 'REDEEM')
                ->first();

            PointMutation::query()->create([
                'member_id' => $member->id,
                'branch_id' => $token->branch_id,
                'receipt_number' => $invoiceNumber,
                'transaction_type_id' => $transactionType?->id,
                'purchase_nominal' => 0,
                'points_issued' => 0,
                'points_redeemed' => $token->held_points,
                'balance_snapshot' => (int) $member->point_balance,
                'transaction_date' => now(),
                'uploaded_at' => now(),
            ]);

            $this->activityLogger->log(
                action: ActivityLogAction::RedeemConfirmation,
                description: 'Konfirmasi redeem poin oleh staff',
                auditable: $invoice,
                ipAddress: $ipAddress,
                before: [
                    'is_used' => false,
                    'actual_stock' => $previousActual,
                    'held_stock' => $previousHeld,
                ],
                after: [
                    'is_used' => true,
                    'actual_stock' => (int) $stock->actual_stock,
                    'held_stock' => (int) $stock->held_stock,
                ],
                actor: $actor,
            );

            $this->notifyMemberRedeemCompleted($member, $invoice, $token);

            return new RedeemConfirmationResult(
                invoiceId: $invoice->id,
                invoiceNumber: $invoiceNumber,
                memberId: $member->id,
                memberName: (string) ($member->user?->full_name ?? '-'),
                memberNumber: (string) $member->member_number,
                rewardName: (string) ($token->reward?->name ?? '-'),
                branchName: (string) ($token->branch?->name ?? '-'),
                pointsRedeemed: (int) $token->held_points,
                newBalance: (int) $member->point_balance,
            );
        });
    }

    private function notifyMemberRedeemCompleted(Member $member, RedeemInvoice $invoice, RedeemToken $token): void
    {
        if ($member->user === null) {
            return;
        }

        try {
            $this->notificationService->notifyUser(
                user: $member->user,
                title: 'Penukaran poin berhasil',
                body: sprintf('Invoice %s — %s', $invoice->invoice_number, $token->reward?->name ?? '-'),
                platforms: [NotificationPlatform::MobileAppPush],
                payload: [
                    'type' => 'redeem_invoice',
                    'invoiceId' => $invoice->id,
                    'invoiceNumber' => $invoice->invoice_number,
                    'path' => '/redeem/'.$invoice->id,
                ],
            );
        } catch (Throwable $exception) {
            Log::warning('Gagal mengantre push redeem selesai.', [
                'invoice_id' => $invoice->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function assertBranchAllowed(User $actor, int $tokenBranchId): void
    {
        if ($actor->role !== Role::StoreManager) {
            return;
        }

        $staffBranchId = $actor->staff?->branch_id;
        if ($staffBranchId !== $tokenBranchId) {
            throw RedeemConfirmationException::branchMismatch();
        }
    }

    private function generateInvoiceNumber(string $branchCode): string
    {
        $prefix = sprintf('INV-%s-%s-', $branchCode, now()->format('Ymd'));
        $last = RedeemInvoice::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $lastSeq = $last !== null ? (int) substr((string) $last, -4) : 0;

        return $prefix.str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
    }
}
