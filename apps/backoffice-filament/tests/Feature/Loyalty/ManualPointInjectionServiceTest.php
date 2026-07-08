<?php

declare(strict_types=1);

use App\Data\Loyalty\ManualPointInjectionData;
use App\Enums\ActivityLogAction;
use App\Enums\Role;
use App\Enums\TierStatus;
use App\Exceptions\Loyalty\ManualPointInjectionException;
use App\Jobs\PersistActivityLogJob;
use App\Models\ConversionRule;
use App\Models\Member;
use App\Models\PointMutation;
use App\Models\TierMember;
use App\Models\TransactionType;
use App\Models\User;
use App\Services\Loyalty\ManualPointInjectionService;
use Database\Seeders\TierMemberSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
});

/**
 * @return array{member: Member, transactionType: TransactionType, actor: User}
 */
function createInjectionFixtures(
    int $pointBalance = 500,
    TierStatus $tier = TierStatus::Silver,
    bool $isSuspended = false,
): array {
    (new TransactionTypeSeeder)->run();
    (new TierMemberSeeder)->run();

    $transactionType = TransactionType::query()->where('type_key', 'PERHIASAN')->firstOrFail();
    $tierMember = TierMember::query()->where('tier_code', $tier)->firstOrFail();

    ConversionRule::query()->updateOrCreate(
        [
            'transaction_type_id' => $transactionType->id,
            'tier_member_id' => $tierMember->id,
        ],
        [
            'conversion_nominal' => '100000.00',
        ],
    );

    $actor = User::factory()->create(['role' => Role::Administrator]);
    $member = Member::factory()->create([
        'current_tier' => $tier,
        'point_balance' => $pointBalance,
        'highest_point' => $pointBalance,
        'is_suspended' => $isSuspended,
    ]);

    return [
        'member' => $member,
        'transactionType' => $transactionType,
        'actor' => $actor,
    ];
}

function makeInjectionData(
    Member $member,
    TransactionType $transactionType,
    string $purchaseNominal = '1500000.00',
    ?string $receiptNumber = null,
): ManualPointInjectionData {
    return ManualPointInjectionData::fromArray([
        'member_id' => $member->id,
        'branch_id' => null,
        'transaction_type_id' => $transactionType->id,
        'purchase_nominal' => $purchaseNominal,
        'receipt_number' => $receiptNumber,
        'transaction_date' => Carbon::today()->toDateString(),
    ]);
}

it('injects points with correct balance snapshot and activity log', function (): void {
    $fixtures = createInjectionFixtures();
    $service = app(ManualPointInjectionService::class);
    $data = makeInjectionData($fixtures['member'], $fixtures['transactionType']);

    $result = $service->inject($data, $fixtures['actor'], '127.0.0.1');

    expect($result->pointsIssued)->toBe(15)
        ->and($result->previousBalance)->toBe(500)
        ->and($result->newBalance)->toBe(515)
        ->and($result->balanceSnapshot)->toBe(515);

    $fixtures['member']->refresh();

    expect($fixtures['member']->point_balance)->toBe(515)
        ->and($fixtures['member']->highest_point)->toBe(515);

    $mutation = PointMutation::query()->findOrFail($result->mutationId);

    expect($mutation->balance_snapshot)->toBe(515)
        ->and($mutation->points_issued)->toBe(15)
        ->and($mutation->points_redeemed)->toBe(0);

    Queue::assertPushed(PersistActivityLogJob::class, function (PersistActivityLogJob $job) use ($mutation, $fixtures): bool {
        return $job->data->auditableId === $mutation->id
            && $job->data->userId === $fixtures['actor']->id
            && $job->data->beforeJson['point_balance'] === 500
            && $job->data->afterJson['point_balance'] === 515
            && $job->data->action === ActivityLogAction::ManualPointInjection;
    });
});

it('rejects suspended members and rolls back mutation', function (): void {
    $fixtures = createInjectionFixtures(isSuspended: true);
    $service = app(ManualPointInjectionService::class);
    $initialMutations = PointMutation::query()->count();

    expect(fn () => $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType']),
        $fixtures['actor'],
        '127.0.0.1',
    ))->toThrow(ManualPointInjectionException::class, 'ditangguhkan');

    expect(PointMutation::query()->count())->toBe($initialMutations);

    Queue::assertNotPushed(PersistActivityLogJob::class);
});

it('rejects nominal below conversion minimum', function (): void {
    $fixtures = createInjectionFixtures();
    $service = app(ManualPointInjectionService::class);
    $initialMutations = PointMutation::query()->count();

    expect(fn () => $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '50000.00'),
        $fixtures['actor'],
        '127.0.0.1',
    ))->toThrow(ManualPointInjectionException::class);

    expect(PointMutation::query()->count())->toBe($initialMutations);

    Queue::assertNotPushed(PersistActivityLogJob::class);
});

it('rejects duplicate receipt for the same transaction type globally', function (): void {
    $fixtures = createInjectionFixtures();
    $service = app(ManualPointInjectionService::class);
    $reference = 'STRUK-UNIQUE-001';

    $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '100000.00', $reference),
        $fixtures['actor'],
        '127.0.0.1',
    );

    expect(fn () => $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '100000.00', $reference),
        $fixtures['actor'],
        '127.0.0.1',
    ))->toThrow(ManualPointInjectionException::class, 'struk');
});

it('upgrades tier when new balance crosses threshold', function (): void {
    $fixtures = createInjectionFixtures(pointBalance: 1000);
    $service = app(ManualPointInjectionService::class);

    $result = $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '100000.00'),
        $fixtures['actor'],
        '127.0.0.1',
    );

    expect($result->tierUpgraded)->toBeTrue()
        ->and($result->newTier)->toBe(TierStatus::Gold);

    $fixtures['member']->refresh();

    expect($fixtures['member']->current_tier)->toBe(TierStatus::Gold);
});

it('keeps highest point when new balance is lower than previous highest', function (): void {
    $fixtures = createInjectionFixtures(pointBalance: 500);
    $fixtures['member']->update(['highest_point' => 2000]);
    $service = app(ManualPointInjectionService::class);

    $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '100000.00'),
        $fixtures['actor'],
        '127.0.0.1',
    );

    $fixtures['member']->refresh();

    expect($fixtures['member']->point_balance)->toBe(501)
        ->and($fixtures['member']->highest_point)->toBe(2000);
});

it('allows multiple mutations without receipt number', function (): void {
    $fixtures = createInjectionFixtures();
    $service = app(ManualPointInjectionService::class);
    $initialMutations = PointMutation::query()->count();

    $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '100000.00'),
        $fixtures['actor'],
        '127.0.0.1',
    );

    $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType'], '100000.00'),
        $fixtures['actor'],
        '127.0.0.1',
    );

    expect(PointMutation::query()->count())->toBe($initialMutations + 2);
});

it('updates last activity at to transaction date', function (): void {
    $fixtures = createInjectionFixtures();
    $service = app(ManualPointInjectionService::class);
    $transactionDate = Carbon::today()->subDays(3);

    $data = ManualPointInjectionData::fromArray([
        'member_id' => $fixtures['member']->id,
        'branch_id' => null,
        'transaction_type_id' => $fixtures['transactionType']->id,
        'purchase_nominal' => '100000.00',
        'receipt_number' => null,
        'transaction_date' => $transactionDate->toDateString(),
    ]);

    $service->inject($data, $fixtures['actor'], '127.0.0.1');

    $fixtures['member']->refresh();

    expect($fixtures['member']->last_activity_at?->toDateString())->toBe($transactionDate->toDateString());
});

it('rejects future transaction dates', function (): void {
    $fixtures = createInjectionFixtures();
    $service = app(ManualPointInjectionService::class);

    $data = ManualPointInjectionData::fromArray([
        'member_id' => $fixtures['member']->id,
        'branch_id' => null,
        'transaction_type_id' => $fixtures['transactionType']->id,
        'purchase_nominal' => '100000.00',
        'receipt_number' => null,
        'transaction_date' => Carbon::tomorrow()->toDateString(),
    ]);

    expect(fn () => $service->inject($data, $fixtures['actor'], '127.0.0.1'))
        ->toThrow(ManualPointInjectionException::class, 'Tanggal transaksi');

    Queue::assertNotPushed(PersistActivityLogJob::class);
});
