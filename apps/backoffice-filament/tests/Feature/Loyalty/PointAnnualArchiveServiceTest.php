<?php

declare(strict_types=1);

use App\Enums\ActivityLogAction;
use App\Enums\Role;
use App\Exceptions\Loyalty\PointAnnualArchiveException;
use App\Jobs\PersistActivityLogJob;
use App\Models\Member;
use App\Models\PointAnnualArchive;
use App\Models\PointAnnualArchivePeriod;
use App\Models\PointMutation;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogger;
use App\Services\Loyalty\PointAnnualArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake([PersistActivityLogJob::class]);
});

it('archives previous year, resets balances, and aggregates mutation summary', function (): void {
    $admin = User::factory()->administrator()->create();

    $memberA = Member::factory()->create([
        'point_balance' => 120,
        'highest_point' => 300,
    ]);
    $memberB = Member::factory()->create([
        'point_balance' => 80,
        'highest_point' => 180,
    ]);

    $targetYear = now()->subYear()->year;

    PointMutation::query()->create([
        'member_id' => $memberA->id,
        'branch_id' => null,
        'source_id' => null,
        'receipt_number' => null,
        'transaction_type_id' => null,
        'purchase_nominal' => '0.00',
        'points_issued' => 40,
        'points_redeemed' => 10,
        'balance_snapshot' => 120,
        'transaction_date' => Carbon::create($targetYear, 2, 14),
        'uploaded_at' => now(),
    ]);

    PointMutation::query()->create([
        'member_id' => $memberB->id,
        'branch_id' => null,
        'source_id' => null,
        'receipt_number' => null,
        'transaction_type_id' => null,
        'purchase_nominal' => '0.00',
        'points_issued' => 20,
        'points_redeemed' => 5,
        'balance_snapshot' => 80,
        'transaction_date' => Carbon::create($targetYear, 8, 1),
        'uploaded_at' => now(),
    ]);

    // Outside target year, should be excluded from yearly summary.
    PointMutation::query()->create([
        'member_id' => $memberA->id,
        'branch_id' => null,
        'source_id' => null,
        'receipt_number' => null,
        'transaction_type_id' => null,
        'purchase_nominal' => '0.00',
        'points_issued' => 999,
        'points_redeemed' => 888,
        'balance_snapshot' => 120,
        'transaction_date' => Carbon::create($targetYear - 1, 12, 31),
        'uploaded_at' => now(),
    ]);

    $period = app(PointAnnualArchiveService::class)->archive($admin, '127.0.0.1', $targetYear);

    expect($period->archive_year)->toBe($targetYear)
        ->and($period->total_members)->toBe(2)
        ->and($period->frozen_points_total)->toBe(200)
        ->and($period->earned_points_total)->toBe(60)
        ->and($period->redeemed_points_total)->toBe(15)
        ->and($period->archived_at)->not->toBeNull();

    expect(PointAnnualArchive::query()->where('period_id', $period->id)->count())->toBe(2);

    $memberA->refresh();
    $memberB->refresh();

    expect($memberA->point_balance)->toBe(0)
        ->and($memberB->point_balance)->toBe(0)
        ->and($memberA->highest_point)->toBe(0)
        ->and($memberB->highest_point)->toBe(0);

    $resetMutations = PointMutation::query()
        ->whereNull('transaction_type_id')
        ->whereNull('source_id')
        ->whereIn('member_id', [$memberA->id, $memberB->id])
        ->where('balance_snapshot', 0)
        ->where('points_issued', 0)
        ->orderBy('uploaded_at', 'desc')
        ->limit(2)
        ->get();

    expect($resetMutations)->toHaveCount(2);

    Queue::assertPushed(PersistActivityLogJob::class, function (PersistActivityLogJob $job) use ($period): bool {
        return $job->data->auditableType === 'PointAnnualArchivePeriod'
            && $job->data->auditableId === $period->id
            && $job->data->action === ActivityLogAction::PointAnnualArchive;
    });

    $lastRunStatus = app(PointAnnualArchiveService::class)->getLastRunStatus();
    expect($lastRunStatus['status'])->toBe('success')
        ->and($lastRunStatus['target_year'])->toBe($targetYear)
        ->and($lastRunStatus['total_members'])->toBe(2);
});

it('blocks rerun for the same archive year', function (): void {
    $admin = User::factory()->administrator()->create();
    Member::factory()->create(['point_balance' => 10]);
    $targetYear = now()->subYear()->year;
    $service = app(PointAnnualArchiveService::class);

    $service->archive($admin, '127.0.0.1', $targetYear);

    expect(fn () => $service->archive($admin, '127.0.0.1', $targetYear))
        ->toThrow(PointAnnualArchiveException::class, 'sudah pernah');
});

it('allows only administrator to execute archive', function (): void {
    $marketing = User::factory()->create(['role' => Role::Marketing]);
    Member::factory()->create(['point_balance' => 10]);
    $targetYear = now()->subYear()->year;

    expect(fn () => app(PointAnnualArchiveService::class)->archive($marketing, '127.0.0.1', $targetYear))
        ->toThrow(PointAnnualArchiveException::class, 'administrator');

    expect(PointAnnualArchivePeriod::query()->count())->toBe(0);
});

it('rolls back all changes when archive process fails', function (): void {
    $admin = User::factory()->administrator()->create();
    $member = Member::factory()->create([
        'point_balance' => 75,
        'highest_point' => 150,
    ]);
    $targetYear = now()->subYear()->year;

    $failingLogger = Mockery::mock(ActivityLogger::class);
    $failingLogger
        ->shouldReceive('log')
        ->once()
        ->andThrow(new \RuntimeException('forced failure'));
    app()->instance(ActivityLogger::class, $failingLogger);

    $service = app(PointAnnualArchiveService::class);

    expect(fn () => $service->archive($admin, '127.0.0.1', $targetYear))
        ->toThrow(\RuntimeException::class, 'forced failure');

    $member->refresh();

    expect(PointAnnualArchivePeriod::query()->count())->toBe(0)
        ->and(PointAnnualArchive::query()->count())->toBe(0)
        ->and($member->point_balance)->toBe(75)
        ->and($member->highest_point)->toBe(150);

    $service->markRunFailed($admin, $targetYear, 'forced failure');
    $lastRunStatus = $service->getLastRunStatus();
    expect($lastRunStatus['status'])->toBe('failed')
        ->and($lastRunStatus['error'])->toContain('forced failure');
});

it('marks stale queued or processing runs as failed', function (): void {
    $admin = User::factory()->administrator()->create();
    $service = app(PointAnnualArchiveService::class);
    $targetYear = now()->subYear()->year;

    $service->markRunQueued($admin, $targetYear);

    Cache::forever('point-annual-archive:last-run', [
        'status' => 'processing',
        'target_year' => $targetYear,
        'started_at' => now()->subMinutes(30)->toIso8601String(),
        'completed_at' => null,
        'total_members' => null,
        'frozen_points_total' => null,
        'error' => null,
        'requested_by' => $admin->full_name,
    ]);

    $status = $service->getLastRunStatus();

    expect($status['status'])->toBe('failed')
        ->and($status['error'])->toContain('terhenti')
        ->and($service->isRunInProgress())->toBeFalse();
});
