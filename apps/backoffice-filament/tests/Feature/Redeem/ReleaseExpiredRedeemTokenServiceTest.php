<?php

declare(strict_types=1);

use App\Enums\ActivityLogAction;
use App\Jobs\PersistActivityLogJob;
use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Models\Member;
use App\Models\RedeemToken;
use App\Models\Reward;
use App\Services\Redeem\ReleaseExpiredRedeemTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
});

/**
 * @return array{
 *     branch: Branch,
 *     reward: Reward,
 *     stock: BranchRewardStock,
 *     member: Member,
 *     token: RedeemToken
 * }
 */
function createReleaseFixtures(
    bool $expired = true,
    bool $used = false,
    ?\Illuminate\Support\Carbon $releasedAt = null,
): array {
    $branch = Branch::factory()->create(['address_id' => null]);
    $reward = Reward::factory()->create(['points_required' => 1000]);

    $stock = BranchRewardStock::factory()->create([
        'branch_id' => $branch->id,
        'reward_id' => $reward->id,
        'actual_stock' => 5,
        'held_stock' => 1,
    ]);

    $member = Member::factory()->create([
        'point_balance' => 4000,
        'highest_point' => 5000,
    ]);

    $token = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'token_code' => 'EXP123XYZ9',
        'held_points' => 1000,
        'is_used' => $used,
        'expired_at' => $expired ? now()->subMinutes(5) : now()->addMinutes(30),
        'released_at' => $releasedAt,
    ]);

    return [
        'branch' => $branch,
        'reward' => $reward,
        'stock' => $stock,
        'member' => $member,
        'token' => $token,
    ];
}

it('releases expired unused token: points back, held down, sets released_at', function (): void {
    $fx = createReleaseFixtures();
    $service = app(ReleaseExpiredRedeemTokenService::class);

    $count = $service->releaseExpired();

    expect($count)->toBe(1);

    $fx['token']->refresh();
    $fx['member']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->released_at)->not->toBeNull()
        ->and($fx['token']->is_used)->toBeFalse()
        ->and($fx['member']->point_balance)->toBe(5000)
        ->and($fx['stock']->held_stock)->toBe(0)
        ->and($fx['stock']->actual_stock)->toBe(5);

    Queue::assertPushed(PersistActivityLogJob::class, function (PersistActivityLogJob $job) use ($fx): bool {
        return $job->data->auditableId === $fx['token']->id
            && $job->data->action === ActivityLogAction::RedeemTokenExpiredRelease;
    });
});

it('does not double-refund on second run', function (): void {
    $fx = createReleaseFixtures();
    $service = app(ReleaseExpiredRedeemTokenService::class);

    expect($service->releaseExpired())->toBe(1);
    expect($service->releaseExpired())->toBe(0);

    $fx['token']->refresh();
    $fx['member']->refresh();
    $fx['stock']->refresh();

    expect($fx['member']->point_balance)->toBe(5000)
        ->and($fx['stock']->held_stock)->toBe(0)
        ->and($fx['token']->released_at)->not->toBeNull();
});

it('skips token still within expiry window', function (): void {
    $fx = createReleaseFixtures(expired: false);
    $service = app(ReleaseExpiredRedeemTokenService::class);

    expect($service->releaseExpired())->toBe(0);

    $fx['token']->refresh();
    $fx['member']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->released_at)->toBeNull()
        ->and($fx['member']->point_balance)->toBe(4000)
        ->and($fx['stock']->held_stock)->toBe(1);
});

it('skips already used token', function (): void {
    $fx = createReleaseFixtures(expired: true, used: true);
    $service = app(ReleaseExpiredRedeemTokenService::class);

    expect($service->releaseExpired())->toBe(0);

    $fx['token']->refresh();
    $fx['member']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->released_at)->toBeNull()
        ->and($fx['member']->point_balance)->toBe(4000)
        ->and($fx['stock']->held_stock)->toBe(1);
});

it('command redeem:release-expired-tokens invokes service', function (): void {
    createReleaseFixtures();

    Artisan::call('redeem:release-expired-tokens');

    expect(Artisan::output())->toContain('Released 1 expired redeem tokens.');
});

it('refunds points when stock row is missing', function (): void {
    $fx = createReleaseFixtures();
    $fx['stock']->delete();

    $service = app(ReleaseExpiredRedeemTokenService::class);
    expect($service->releaseExpired())->toBe(1);

    $fx['token']->refresh();
    $fx['member']->refresh();

    expect($fx['token']->released_at)->not->toBeNull()
        ->and($fx['member']->point_balance)->toBe(5000);
});

it('refunds points when held_stock already zero without underflow', function (): void {
    $fx = createReleaseFixtures();
    $fx['stock']->update(['held_stock' => 0]);

    $service = app(ReleaseExpiredRedeemTokenService::class);
    expect($service->releaseExpired())->toBe(1);

    $fx['token']->refresh();
    $fx['member']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->released_at)->not->toBeNull()
        ->and($fx['member']->point_balance)->toBe(5000)
        ->and($fx['stock']->held_stock)->toBe(0);
});

it('release vs used-token race: once used, release is no-op without double money move', function (): void {
    // ponytail: simulate "confirm won the race" then release runs — no second refund.
    $fx = createReleaseFixtures(expired: true, used: false);
    $fx['token']->update(['is_used' => true]);

    $balanceBefore = $fx['member']->point_balance;
    $heldBefore = $fx['stock']->held_stock;

    $service = app(ReleaseExpiredRedeemTokenService::class);
    expect($service->releaseExpired())->toBe(0);

    $fx['member']->refresh();
    $fx['stock']->refresh();
    $fx['token']->refresh();

    expect($fx['member']->point_balance)->toBe($balanceBefore)
        ->and($fx['stock']->held_stock)->toBe($heldBefore)
        ->and($fx['token']->released_at)->toBeNull();
});
