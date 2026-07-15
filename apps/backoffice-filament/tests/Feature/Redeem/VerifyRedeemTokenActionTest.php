<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Filament\Resources\RedeemTokens\Support\VerifyRedeemTokenFormSupport;
use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Models\Member;
use App\Models\PointMutation;
use App\Models\RedeemInvoice;
use App\Models\RedeemToken;
use App\Models\Reward;
use App\Models\Staff;
use App\Models\User;
use App\Services\Redeem\FonnteOtpClient;
use App\Services\Redeem\RedeemConfirmationService;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();

    $this->mock(FonnteOtpClient::class, function ($mock): void {
        $mock->shouldReceive('verify')->andReturnNull();
        $mock->shouldReceive('send')->andReturnNull();
    });
});

/**
 * @return array{
 *     branch: Branch,
 *     stock: BranchRewardStock,
 *     member: Member,
 *     token: RedeemToken,
 *     actor: User
 * }
 */
function createVerifyActionFixtures(): array
{
    (new TransactionTypeSeeder)->run();

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
        'phone_number' => '081234567890',
    ]);
    $actor = User::factory()->staffRole(Role::Administrator)->create();
    Staff::factory()->create([
        'user_id' => $actor->id,
        'branch_id' => $branch->id,
    ]);
    $token = RedeemToken::factory()->create([
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'token_code' => 'WIZARDTOK1',
        'held_points' => 1000,
        'is_used' => false,
        'expired_at' => now()->addMinutes(30),
    ]);

    return [
        'branch' => $branch,
        'stock' => $stock,
        'member' => $member,
        'token' => $token,
        'actor' => $actor->fresh(['staff']),
    ];
}

it('wizard happy path via FormSupport + confirmation service', function (): void {
    $fx = createVerifyActionFixtures();

    $found = VerifyRedeemTokenFormSupport::findAvailableToken($fx['token']->token_code);
    expect($found)->not->toBeNull()
        ->and($found?->id)->toBe($fx['token']->id);

    $preview = VerifyRedeemTokenFormSupport::buildPreviewHtml([
        'token_code' => $fx['token']->token_code,
    ]);
    expect($preview)->toContain($fx['token']->token_code)
        ->and($preview)->toContain((string) $fx['member']->member_number);

    $service = app(RedeemConfirmationService::class);
    $result = $service->confirm(
        $fx['token']->token_code,
        '123456',
        $fx['actor'],
        '127.0.0.1',
    );

    expect($result->invoiceNumber)->toStartWith('INV-')
        ->and(RedeemInvoice::query()->count())->toBe(1)
        ->and(PointMutation::query()->count())->toBe(1);

    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeTrue();

    // After confirm, FormSupport no longer returns it as available
    expect(VerifyRedeemTokenFormSupport::findAvailableToken($fx['token']->token_code))->toBeNull();
});

it('double-submit confirm does not create duplicate invoice', function (): void {
    $fx = createVerifyActionFixtures();
    $service = app(RedeemConfirmationService::class);

    $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1');

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Token redeem sudah digunakan.');

    expect(RedeemInvoice::query()->count())->toBe(1)
        ->and(PointMutation::query()->count())->toBe(1);

    $fx['stock']->refresh();
    expect($fx['stock']->actual_stock)->toBe(4)
        ->and($fx['stock']->held_stock)->toBe(0);
});
