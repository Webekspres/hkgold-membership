<?php

declare(strict_types=1);

use App\Jobs\ProcessPointAnnualArchiveJob;
use App\Models\Member;
use App\Models\PointAnnualArchivePeriod;
use App\Models\User;
use App\Services\Loyalty\PointAnnualArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('processes annual archive via job', function (): void {
    $admin = User::factory()->administrator()->create();
    $member = Member::factory()->create([
        'point_balance' => 55,
        'highest_point' => 99,
    ]);
    $targetYear = now()->subYear()->year;

    ProcessPointAnnualArchiveJob::dispatchSync($admin, '127.0.0.1', $targetYear);

    $member->refresh();
    $period = PointAnnualArchivePeriod::query()->where('archive_year', $targetYear)->first();

    expect($period)->not->toBeNull()
        ->and($period?->total_members)->toBe(1)
        ->and($period?->frozen_points_total)->toBe(55)
        ->and($member->point_balance)->toBe(0)
        ->and($member->highest_point)->toBe(0);
});

it('marks failed status when job failed callback is invoked', function (): void {
    Cache::forget('point-annual-archive:last-run');

    $admin = User::factory()->administrator()->create();
    $targetYear = now()->subYear()->year;
    $job = new ProcessPointAnnualArchiveJob($admin, '127.0.0.1', $targetYear);

    $job->failed(new RuntimeException('worker crashed'));

    $status = app(PointAnnualArchiveService::class)->getLastRunStatus();

    expect($status['status'])->toBe('failed')
        ->and($status['error'])->toContain('worker crashed');
});
