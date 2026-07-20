<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Models\Member;
use App\Models\RedeemToken;
use App\Models\Reward;
use App\Models\Staff;
use App\Models\User;
use App\Services\Redeem\RedeemConfirmationService;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    (new TransactionTypeSeeder)->run();
});

it('scopeAvailable excludes tokens with released_at set', function (): void {
    $branch = Branch::factory()->create(['address_id' => null]);
    $reward = Reward::factory()->create(['points_required' => 1000]);
    $member = Member::factory()->create();

    BranchRewardStock::factory()->create([
        'branch_id' => $branch->id,
        'reward_id' => $reward->id,
        'actual_stock' => 5,
        'held_stock' => 1,
    ]);

    $available = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'is_used' => false,
        'expired_at' => now()->addHour(),
        'released_at' => null,
    ]);

    $released = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'is_used' => false,
        'expired_at' => now()->addHour(),
        'released_at' => now(),
    ]);

    $expired = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'is_used' => false,
        'expired_at' => now()->subMinute(),
        'released_at' => null,
    ]);

    $used = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'is_used' => true,
        'expired_at' => now()->addHour(),
        'released_at' => null,
    ]);

    $ids = RedeemToken::query()->available()->pluck('id')->all();

    expect($ids)->toContain($available->id)
        ->and($ids)->not->toContain($released->id)
        ->and($ids)->not->toContain($expired->id)
        ->and($ids)->not->toContain($used->id);
});

it('cancel flow then confirm throws TOKEN_RELEASED without writes', function (): void {
    $branch = Branch::factory()->create(['address_id' => null]);
    $reward = Reward::factory()->create(['points_required' => 1000]);
    $stock = BranchRewardStock::factory()->create([
        'branch_id' => $branch->id,
        'reward_id' => $reward->id,
        'actual_stock' => 5,
        'held_stock' => 1,
    ]);
    $member = Member::factory()->create([
        'point_balance' => 3000,
        'highest_point' => 5000,
    ]);

    $token = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'held_points' => 1000,
        'is_used' => false,
        'expired_at' => now()->addHour(),
        'released_at' => null,
    ]);

    $actor = User::factory()->staffRole(Role::Administrator)->create();
    Staff::factory()->create([
        'user_id' => $actor->id,
        'branch_id' => $branch->id,
    ]);

    // Simulasi cancel API: refund poin, turunkan held, set released_at
    $member->update(['point_balance' => $member->point_balance + $token->held_points]);
    $stock->update(['held_stock' => max(0, $stock->held_stock - 1)]);
    $token->update(['released_at' => now()]);

    $balanceBefore = (int) $member->fresh()->point_balance;
    $heldBefore = (int) $stock->fresh()->held_stock;

    $service = app(RedeemConfirmationService::class);

    try {
        $service->confirm($token->token_code, '123456', $actor, '127.0.0.1');
        expect(false)->toBeTrue('confirm should throw');
    } catch (RedeemConfirmationException $e) {
        expect($e->errorCode)->toBe('TOKEN_RELEASED')
            ->and($e->getMessage())->toBe('Token redeem sudah dibatalkan.');
    }

    expect((int) $member->fresh()->point_balance)->toBe($balanceBefore)
        ->and((int) $stock->fresh()->held_stock)->toBe($heldBefore)
        ->and($token->fresh()->is_used)->toBeFalse();
});
