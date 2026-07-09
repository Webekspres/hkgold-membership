<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Enums\ActivityLogAction;
use App\Enums\Role;
use App\Exceptions\Loyalty\PointAnnualArchiveException;
use App\Models\Member;
use App\Models\PointAnnualArchive;
use App\Models\PointAnnualArchivePeriod;
use App\Models\PointMutation;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PointAnnualArchiveService
{
    private const LAST_RUN_CACHE_KEY = 'point-annual-archive:last-run';

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function resolveTargetYear(): int
    {
        return now()->subYear()->year;
    }

    public function canArchiveYear(int $archiveYear): bool
    {
        return ! PointAnnualArchivePeriod::query()
            ->where('archive_year', $archiveYear)
            ->exists();
    }

    public function markRunQueued(User $actor, int $archiveYear): void
    {
        Cache::forever(self::LAST_RUN_CACHE_KEY, [
            'status' => 'queued',
            'target_year' => $archiveYear,
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
            'total_members' => null,
            'frozen_points_total' => null,
            'error' => null,
            'requested_by' => $actor->full_name ?: $actor->email,
        ]);
    }

    public function markRunProcessing(User $actor, int $archiveYear): void
    {
        Cache::forever(self::LAST_RUN_CACHE_KEY, [
            'status' => 'processing',
            'target_year' => $archiveYear,
            'started_at' => now()->toIso8601String(),
            'completed_at' => null,
            'total_members' => null,
            'frozen_points_total' => null,
            'error' => null,
            'requested_by' => $actor->full_name ?: $actor->email,
        ]);
    }

    public function markRunFailed(User $actor, int $archiveYear, string $message): void
    {
        $existing = Cache::get(self::LAST_RUN_CACHE_KEY);

        Cache::forever(self::LAST_RUN_CACHE_KEY, [
            'status' => 'failed',
            'target_year' => $archiveYear,
            'started_at' => $existing['started_at'] ?? now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
            'total_members' => null,
            'frozen_points_total' => null,
            'error' => $message,
            'requested_by' => $actor->full_name ?: $actor->email,
        ]);
    }

    public function markRunSuccess(PointAnnualArchivePeriod $period, User $actor): void
    {
        $existing = Cache::get(self::LAST_RUN_CACHE_KEY);

        Cache::forever(self::LAST_RUN_CACHE_KEY, [
            'status' => 'success',
            'target_year' => (int) $period->archive_year,
            'started_at' => $existing['started_at'] ?? $period->archived_at?->toIso8601String(),
            'completed_at' => $period->archived_at?->toIso8601String(),
            'total_members' => (int) $period->total_members,
            'frozen_points_total' => (int) $period->frozen_points_total,
            'error' => null,
            'requested_by' => $actor->full_name ?: $actor->email,
        ]);
    }

    /**
     * @return array{
     *   status: string,
     *   target_year: int,
     *   started_at: string|null,
     *   completed_at: string|null,
     *   total_members: int|null,
     *   frozen_points_total: int|null,
     *   error: string|null,
     *   requested_by: string|null
     * }
     */
    public function getLastRunStatus(): array
    {
        $cached = Cache::get(self::LAST_RUN_CACHE_KEY);
        if (is_array($cached)) {
            return array_merge([
                'status' => 'idle',
                'target_year' => $this->resolveTargetYear(),
                'started_at' => null,
                'completed_at' => null,
                'total_members' => null,
                'frozen_points_total' => null,
                'error' => null,
                'requested_by' => null,
            ], $cached);
        }

        $latestPeriod = PointAnnualArchivePeriod::query()
            ->whereNotNull('archived_at')
            ->orderByDesc('archived_at')
            ->first();

        if ($latestPeriod !== null) {
            return [
                'status' => 'success',
                'target_year' => (int) $latestPeriod->archive_year,
                'started_at' => null,
                'completed_at' => $latestPeriod->archived_at?->toIso8601String(),
                'total_members' => (int) $latestPeriod->total_members,
                'frozen_points_total' => (int) $latestPeriod->frozen_points_total,
                'error' => null,
                'requested_by' => null,
            ];
        }

        return [
            'status' => 'idle',
            'target_year' => $this->resolveTargetYear(),
            'started_at' => null,
            'completed_at' => null,
            'total_members' => null,
            'frozen_points_total' => null,
            'error' => null,
            'requested_by' => null,
        ];
    }

    public function archive(User $actor, string $ipAddress, ?int $archiveYear = null): PointAnnualArchivePeriod
    {
        if ($actor->role !== Role::Administrator) {
            throw PointAnnualArchiveException::actorMustBeAdministrator();
        }

        $targetYear = $archiveYear ?? $this->resolveTargetYear();
        $expectedYear = $this->resolveTargetYear();

        if ($targetYear !== $expectedYear) {
            throw PointAnnualArchiveException::onlyPreviousYearAllowed($expectedYear);
        }

        return DB::transaction(function () use ($actor, $ipAddress, $targetYear): PointAnnualArchivePeriod {
            $existingPeriod = PointAnnualArchivePeriod::query()
                ->where('archive_year', $targetYear)
                ->lockForUpdate()
                ->first();

            if ($existingPeriod !== null) {
                throw PointAnnualArchiveException::archiveAlreadyExists($targetYear);
            }

            $period = PointAnnualArchivePeriod::query()->create([
                'archive_year' => $targetYear,
                'name' => 'Arsip Poin '.$targetYear,
                'total_members' => 0,
                'frozen_points_total' => 0,
                'earned_points_total' => 0,
                'redeemed_points_total' => 0,
                'archived_at' => null,
            ]);

            $yearStart = Carbon::create($targetYear, 1, 1)->startOfDay();
            $yearEnd = Carbon::create($targetYear, 12, 31)->endOfDay();
            $archiveTimestamp = now();

            $earnedPointsTotal = (int) PointMutation::query()
                ->whereBetween('transaction_date', [$yearStart, $yearEnd])
                ->sum('points_issued');

            $redeemedPointsTotal = (int) PointMutation::query()
                ->whereBetween('transaction_date', [$yearStart, $yearEnd])
                ->sum('points_redeemed');

            $processedMembers = 0;
            $frozenPointsTotal = 0;

            Member::query()
                ->select(['id'])
                ->orderBy('id')
                ->chunkById(200, function ($members) use (
                    $period,
                    $archiveTimestamp,
                    &$processedMembers,
                    &$frozenPointsTotal
                ): void {
                    $memberIds = $members->pluck('id');

                    $lockedMembers = Member::query()
                        ->whereIn('id', $memberIds)
                        ->lockForUpdate()
                        ->get(['id', 'point_balance', 'highest_point', 'current_tier'])
                        ->keyBy('id');

                    foreach ($memberIds as $memberId) {
                        /** @var Member|null $member */
                        $member = $lockedMembers->get($memberId);

                        if ($member === null) {
                            continue;
                        }

                        $previousBalance = (int) $member->point_balance;

                        PointAnnualArchive::query()->create([
                            'period_id' => $period->id,
                            'member_id' => $member->id,
                            'frozen_points_total' => $previousBalance,
                            'highest_point' => (int) $member->highest_point,
                            'last_tier_position' => $member->current_tier,
                            'frozen_at' => $archiveTimestamp,
                        ]);

                        PointMutation::query()->create([
                            'member_id' => $member->id,
                            'branch_id' => null,
                            'source_id' => null,
                            'receipt_number' => null,
                            'transaction_type_id' => null,
                            'purchase_nominal' => '0.00',
                            'points_issued' => 0,
                            'points_redeemed' => $previousBalance,
                            'balance_snapshot' => 0,
                            'transaction_date' => $archiveTimestamp,
                            'uploaded_at' => $archiveTimestamp,
                        ]);

                        $member->update([
                            'point_balance' => 0,
                            'highest_point' => 0,
                        ]);

                        $processedMembers++;
                        $frozenPointsTotal += $previousBalance;
                    }
                });

            $period->update([
                'total_members' => $processedMembers,
                'frozen_points_total' => $frozenPointsTotal,
                'earned_points_total' => $earnedPointsTotal,
                'redeemed_points_total' => $redeemedPointsTotal,
                'archived_at' => $archiveTimestamp,
            ]);

            $this->activityLogger->log(
                action: ActivityLogAction::PointAnnualArchive,
                description: 'Proses arsip poin tahunan dijalankan',
                auditable: $period,
                ipAddress: $ipAddress,
                before: null,
                after: [
                    'archive_year' => $targetYear,
                    'total_members' => $processedMembers,
                    'frozen_points_total' => $frozenPointsTotal,
                    'earned_points_total' => $earnedPointsTotal,
                    'redeemed_points_total' => $redeemedPointsTotal,
                ],
                actor: $actor,
            );

            $finalPeriod = $period->fresh();
            $this->markRunSuccess($finalPeriod, $actor);

            return $finalPeriod;
        });
    }
}
