<?php

declare(strict_types=1);

use App\Data\Loyalty\ManualPointInjectionData;
use App\Enums\Role;
use App\Enums\TierStatus;
use App\Exceptions\Loyalty\ManualPointInjectionException;
use App\Models\ActivityLog;
use App\Models\ConversionRule;
use App\Models\Member;
use App\Models\PointMutation;
use App\Models\TierMember;
use App\Models\TransactionType;
use App\Models\User;
use App\Services\Loyalty\ManualPointInjectionService;
use Database\Seeders\TierMemberSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

uses(DatabaseTransactions::class);

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

    $log = ActivityLog::query()->where('auditable_id', $mutation->id)->first();

    expect($log)->not->toBeNull()
        ->and($log?->user_id)->toBe($fixtures['actor']->id)
        ->and($log?->before_json['point_balance'])->toBe(500)
        ->and($log?->after_json['point_balance'])->toBe(515);
});

it('rejects suspended members and rolls back mutation', function (): void {
    $fixtures = createInjectionFixtures(isSuspended: true);
    $service = app(ManualPointInjectionService::class);
    $initialMutations = PointMutation::query()->count();
    $initialLogs = ActivityLog::query()->count();

    expect(fn () => $service->inject(
        makeInjectionData($fixtures['member'], $fixtures['transactionType']),
        $fixtures['actor'],
        '127.0.0.1',
    ))->toThrow(ManualPointInjectionException::class, 'ditangguhkan');

    expect(PointMutation::query()->count())->toBe($initialMutations)
        ->and(ActivityLog::query()->count())->toBe($initialLogs);
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
});
