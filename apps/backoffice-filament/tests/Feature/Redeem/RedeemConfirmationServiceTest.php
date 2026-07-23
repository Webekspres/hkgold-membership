<?php

declare(strict_types=1);

use App\Enums\ActivityLogAction;
use App\Enums\NotificationPlatform;
use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Jobs\DeliverNotificationJob;
use App\Jobs\PersistActivityLogJob;
use App\Models\Branch;
use App\Models\BranchRewardStock;
use App\Models\Member;
use App\Models\Notification;
use App\Models\PointMutation;
use App\Models\RedeemInvoice;
use App\Models\RedeemToken;
use App\Models\Reward;
use App\Models\Staff;
use App\Models\User;
use App\Services\Notification\NotificationService;
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
 *     otherBranch: Branch,
 *     reward: Reward,
 *     stock: BranchRewardStock,
 *     member: Member,
 *     token: RedeemToken,
 *     actor: User,
 *     staff: Staff
 * }
 */
function createRedeemFixtures(
    int $actualStock = 5,
    int $heldStock = 1,
    bool $expired = false,
    bool $used = false,
): array {
    (new TransactionTypeSeeder)->run();

    $branch = Branch::factory()->create(['address_id' => null]);
    $otherBranch = Branch::factory()->create(['address_id' => null]);
    $reward = Reward::factory()->create(['points_required' => 1000]);

    $stock = BranchRewardStock::factory()->create([
        'branch_id' => $branch->id,
        'reward_id' => $reward->id,
        'actual_stock' => $actualStock,
        'held_stock' => $heldStock,
    ]);

    $member = Member::factory()->create([
        'point_balance' => 4000,
        'highest_point' => 5000,
        'phone_number' => '081234567890',
    ]);

    $actor = User::factory()->staffRole(Role::Administrator)->create();
    $staff = Staff::factory()->create([
        'user_id' => $actor->id,
        'branch_id' => $branch->id,
    ]);

    $tokenAttributes = [
        'member_id' => $member->id,
        'reward_id' => $reward->id,
        'branch_id' => $branch->id,
        'token_code' => 'ABC123XYZ9',
        'held_points' => 1000,
        'is_used' => $used,
        'expired_at' => $expired ? now()->subMinutes(5) : now()->addMinutes(30),
    ];

    $token = RedeemToken::factory()->create($tokenAttributes);

    return [
        'branch' => $branch,
        'otherBranch' => $otherBranch,
        'reward' => $reward,
        'stock' => $stock,
        'member' => $member,
        'token' => $token,
        'actor' => $actor->fresh(['staff']),
        'staff' => $staff,
    ];
}

it('confirms redeem happy path and writes invoice + mutation + stock', function (): void {
    $fx = createRedeemFixtures();
    $service = app(RedeemConfirmationService::class);

    $result = $service->confirm(
        $fx['token']->token_code,
        '123456',
        $fx['actor'],
        '127.0.0.1',
    );

    expect($result->pointsRedeemed)->toBe(1000)
        ->and($result->invoiceNumber)->toStartWith('INV-'.$fx['branch']->branch_code.'-');

    $fx['token']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->is_used)->toBeTrue()
        ->and($fx['stock']->actual_stock)->toBe(4)
        ->and($fx['stock']->held_stock)->toBe(0);

    $invoice = RedeemInvoice::query()->where('invoice_number', $result->invoiceNumber)->firstOrFail();
    expect($invoice->points_redeemed)->toBe(1000)
        ->and($invoice->member_id)->toBe($fx['member']->id)
        ->and($invoice->staff_id)->toBe($fx['staff']->id)
        ->and($invoice->redeem_token_id)->toBe($fx['token']->id);

    $mutation = PointMutation::query()
        ->where('receipt_number', $result->invoiceNumber)
        ->firstOrFail();

    expect($mutation->points_redeemed)->toBe(1000)
        ->and($mutation->points_issued)->toBe(0)
        ->and($mutation->balance_snapshot)->toBe(4000);

    Queue::assertPushed(PersistActivityLogJob::class, function (PersistActivityLogJob $job) use ($invoice, $fx): bool {
        return $job->data->auditableId === $invoice->id
            && $job->data->userId === $fx['actor']->id
            && $job->data->action === ActivityLogAction::RedeemConfirmation;
    });
});

it('rejects double confirm and does not create duplicate invoice', function (): void {
    $fx = createRedeemFixtures();
    $service = app(RedeemConfirmationService::class);

    $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1');

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Token redeem sudah digunakan.');

    expect(RedeemInvoice::query()->count())->toBe(1)
        ->and(PointMutation::query()->count())->toBe(1);
});

it('rejects expired token without writes', function (): void {
    $fx = createRedeemFixtures(expired: true);
    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Token redeem sudah kedaluwarsa.');

    expect(RedeemInvoice::query()->count())->toBe(0)
        ->and(PointMutation::query()->count())->toBe(0);

    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeFalse();
});

it('rejects released (cancelled) token without writes', function (): void {
    $fx = createRedeemFixtures();
    $fx['token']->update(['released_at' => now()]);

    $balanceBefore = (int) $fx['member']->fresh()->point_balance;
    $actualBefore = (int) $fx['stock']->fresh()->actual_stock;
    $heldBefore = (int) $fx['stock']->fresh()->held_stock;

    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Token redeem sudah dibatalkan.');

    expect(RedeemInvoice::query()->count())->toBe(0)
        ->and(PointMutation::query()->count())->toBe(0);

    $fx['token']->refresh();
    $fx['stock']->refresh();
    $fx['member']->refresh();

    expect($fx['token']->is_used)->toBeFalse()
        ->and($fx['member']->point_balance)->toBe($balanceBefore)
        ->and($fx['stock']->actual_stock)->toBe($actualBefore)
        ->and($fx['stock']->held_stock)->toBe($heldBefore);
});

it('rejects store manager from wrong branch', function (): void {
    $fx = createRedeemFixtures();

    $wrongActor = User::factory()->staffRole(Role::StoreManager)->create();
    Staff::factory()->create([
        'user_id' => $wrongActor->id,
        'branch_id' => $fx['otherBranch']->id,
    ]);
    $wrongActor = $wrongActor->fresh(['staff']);

    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $wrongActor, '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Token redeem tidak untuk cabang Anda.');

    expect(RedeemInvoice::query()->count())->toBe(0)
        ->and(PointMutation::query()->count())->toBe(0);

    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeFalse();
});

it('rejects inconsistent stock and rolls back token state', function (): void {
    $fx = createRedeemFixtures(actualStock: 0, heldStock: 1);
    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Stok reward di cabang tidak konsisten atau habis.');

    expect(RedeemInvoice::query()->count())->toBe(0)
        ->and(PointMutation::query()->count())->toBe(0);

    $fx['token']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->is_used)->toBeFalse()
        ->and($fx['stock']->actual_stock)->toBe(0)
        ->and($fx['stock']->held_stock)->toBe(1);
});

it('rejects unknown token code', function (): void {
    $fx = createRedeemFixtures();
    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm('ZZZZZZZZZZ', '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Token redeem tidak ditemukan.');
});

it('rejects actor without staff', function (): void {
    $fx = createRedeemFixtures();
    $noStaff = User::factory()->staffRole(Role::Administrator)->create();
    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $noStaff, '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Akun Anda belum terhubung ke data staf cabang.');

    expect(RedeemInvoice::query()->count())->toBe(0);
});

it('allows store manager from the same branch', function (): void {
    $fx = createRedeemFixtures();

    $smActor = User::factory()->staffRole(Role::StoreManager)->create();
    Staff::factory()->create([
        'user_id' => $smActor->id,
        'branch_id' => $fx['branch']->id,
    ]);
    $smActor = $smActor->fresh(['staff']);

    $service = app(RedeemConfirmationService::class);
    $result = $service->confirm($fx['token']->token_code, '123456', $smActor, '127.0.0.1');

    expect($result->pointsRedeemed)->toBe(1000)
        ->and(RedeemInvoice::query()->count())->toBe(1);

    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeTrue();
});

it('rejects when stock row is missing', function (): void {
    $fx = createRedeemFixtures();
    $fx['stock']->delete();

    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Stok reward di cabang tidak konsisten atau habis.');

    expect(RedeemInvoice::query()->count())->toBe(0);
    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeFalse();
});

it('rejects when held_stock is zero while actual_stock remains', function (): void {
    $fx = createRedeemFixtures(actualStock: 5, heldStock: 0);
    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'Stok reward di cabang tidak konsisten atau habis.');

    expect(RedeemInvoice::query()->count())->toBe(0);
    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeFalse();
});

it('rejects OTP invalid without writing invoice or mutating stock', function (): void {
    $fx = createRedeemFixtures();

    $this->mock(FonnteOtpClient::class, function ($mock): void {
        $mock->shouldReceive('verify')->andThrow(
            RedeemConfirmationException::otpInvalid('OTP tidak valid'),
        );
        $mock->shouldReceive('send')->andReturnNull();
    });

    $service = app(RedeemConfirmationService::class);

    expect(fn () => $service->confirm($fx['token']->token_code, '000000', $fx['actor'], '127.0.0.1'))
        ->toThrow(RedeemConfirmationException::class, 'OTP tidak valid');

    expect(RedeemInvoice::query()->count())->toBe(0)
        ->and(PointMutation::query()->count())->toBe(0);

    $fx['token']->refresh();
    $fx['stock']->refresh();

    expect($fx['token']->is_used)->toBeFalse()
        ->and($fx['stock']->actual_stock)->toBe(5)
        ->and($fx['stock']->held_stock)->toBe(1);
});

it('double confirm under lock leaves exactly one invoice (race-safe guard)', function (): void {
    $fx = createRedeemFixtures();
    $service = app(RedeemConfirmationService::class);
    $code = $fx['token']->token_code;
    $actor = $fx['actor'];

    // ponytail: SQLite testenv tidak menahan row-lock InnoDB; validasi idempotensi
    // lewat dua panggilan berdekatan di process yang sama (server guard is_used + lockForUpdate).
    $first = null;
    $secondError = null;

    try {
        $first = $service->confirm($code, '123456', $actor, '127.0.0.1');
    } catch (Throwable $e) {
        $secondError = $e;
    }

    try {
        $service->confirm($code, '123456', $actor, '127.0.0.1');
    } catch (Throwable $e) {
        $secondError = $e;
    }

    expect($first)->not->toBeNull()
        ->and($secondError)->toBeInstanceOf(RedeemConfirmationException::class)
        ->and($secondError->getMessage())->toContain('sudah digunakan')
        ->and(RedeemInvoice::query()->count())->toBe(1)
        ->and(PointMutation::query()->count())->toBe(1);

    $fx['stock']->refresh();
    expect($fx['stock']->actual_stock)->toBe(4)
        ->and($fx['stock']->held_stock)->toBe(0);
});

it('enqueues mobile push with redeem_invoice payload after confirm', function (): void {
    $fx = createRedeemFixtures();
    $service = app(RedeemConfirmationService::class);

    $result = $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1');

    $notification = Notification::query()
        ->where('user_id', $fx['member']->user_id)
        ->where('platform', NotificationPlatform::MobileAppPush)
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification?->title)->toBe('Penukaran poin berhasil')
        ->and($notification?->data_payload)->toMatchArray([
            'type' => 'redeem_invoice',
            'invoiceId' => $result->invoiceId,
            'invoiceNumber' => $result->invoiceNumber,
            'path' => '/redeem/'.$result->invoiceId,
        ]);

    Queue::assertPushed(DeliverNotificationJob::class, function (DeliverNotificationJob $job) use ($notification): bool {
        return $job->notificationId === $notification?->id;
    });
});

it('still confirms when notifyUser throws (fail-soft push)', function (): void {
    $fx = createRedeemFixtures();

    $this->mock(NotificationService::class, function ($mock): void {
        $mock->shouldReceive('notifyUser')->andThrow(new RuntimeException('queue down'));
    });

    $service = app(RedeemConfirmationService::class);

    $result = $service->confirm($fx['token']->token_code, '123456', $fx['actor'], '127.0.0.1');

    expect($result->invoiceId)->not->toBeEmpty()
        ->and(RedeemInvoice::query()->count())->toBe(1);

    $fx['token']->refresh();
    expect($fx['token']->is_used)->toBeTrue();
});
