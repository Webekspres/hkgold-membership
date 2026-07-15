<?php

declare(strict_types=1);

namespace App\Services\Redeem;

use App\Enums\ActivityLogAction;
use App\Models\BranchRewardStock;
use App\Models\Member;
use App\Models\RedeemToken;
use App\Services\ActivityLog\ActivityLogger;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredRedeemTokenService
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function releaseExpired(int $chunkSize = 100): int
    {
        $releasedCount = 0;

        RedeemToken::query()
            ->expiredUnused()
            ->orderBy('id')
            ->chunkById($chunkSize, function ($tokens) use (&$releasedCount): void {
                foreach ($tokens as $token) {
                    if ($this->releaseOne($token->id)) {
                        $releasedCount++;
                    }
                }
            });

        return $releasedCount;
    }

    private function releaseOne(string $tokenId): bool
    {
        return DB::transaction(function () use ($tokenId): bool {
            $token = RedeemToken::query()
                ->whereKey($tokenId)
                ->lockForUpdate()
                ->first();

            if (
                $token === null
                || $token->is_used
                || $token->released_at !== null
                || $token->expired_at->isFuture()
            ) {
                return false;
            }

            $member = Member::query()
                ->whereKey($token->member_id)
                ->lockForUpdate()
                ->first();

            if ($member === null) {
                return false;
            }

            $previousBalance = (int) $member->point_balance;
            $previousHeld = 0;

            $member->increment('point_balance', $token->held_points);
            $member->refresh();

            $stock = BranchRewardStock::query()
                ->where('reward_id', $token->reward_id)
                ->where('branch_id', $token->branch_id)
                ->lockForUpdate()
                ->first();

            if ($stock !== null && $stock->held_stock > 0) {
                $previousHeld = (int) $stock->held_stock;
                $stock->decrement('held_stock');
                $stock->refresh();
            }

            $token->update(['released_at' => now()]);

            $this->activityLogger->log(
                action: ActivityLogAction::RedeemTokenExpiredRelease,
                description: 'Rilis hold poin & stok karena token redeem kedaluwarsa',
                auditable: $token,
                ipAddress: '127.0.0.1',
                before: [
                    'point_balance' => $previousBalance,
                    'held_stock' => $previousHeld,
                    'released_at' => null,
                ],
                after: [
                    'point_balance' => (int) $member->point_balance,
                    'held_stock' => $stock !== null ? (int) $stock->held_stock : $previousHeld,
                    'released_at' => $token->released_at?->toIso8601String(),
                    'held_points' => (int) $token->held_points,
                ],
            );

            return true;
        });
    }
}
