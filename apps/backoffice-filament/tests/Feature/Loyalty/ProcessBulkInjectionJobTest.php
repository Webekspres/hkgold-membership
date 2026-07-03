<?php

declare(strict_types=1);

use App\Enums\InjectionStatus;
use App\Enums\TierStatus;
use App\Jobs\ProcessBulkInjectionJob;
use App\Models\ConversionRule;
use App\Models\Media;
use App\Models\Member;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use App\Models\TierMember;
use App\Models\TransactionType;
use Database\Seeders\TierMemberSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

uses(DatabaseTransactions::class);

/**
 * @return array{member: Member, transactionType: TransactionType}
 */
function createBulkInjectionFixtures(
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

    $member = Member::factory()->create([
        'current_tier' => $tier,
        'point_balance' => $pointBalance,
        'highest_point' => $pointBalance,
        'is_suspended' => $isSuspended,
    ]);

    return [
        'member' => $member,
        'transactionType' => $transactionType,
    ];
}

function createBulkInjectionBatchFromCsv(string $csvContent): PointInjectionBatch
{
    Storage::fake('r2');

    $storagePath = 'imports/test/'.uniqid('bulk_', true).'.csv';
    Storage::disk('r2')->put($storagePath, $csvContent);

    $media = Media::query()->create([
        'caption' => 'bulk-injection_test',
        'file_name' => $storagePath,
        'file_type' => 'text/csv',
        'file_url' => Storage::disk('r2')->url($storagePath),
        'file_size' => strlen($csvContent),
    ]);

    return PointInjectionBatch::query()->create([
        'staff_id' => null,
        'media_id' => $media->id,
        'resolved' => false,
        'uploaded_at' => now(),
    ]);
}

function bulkCsvHeader(): string
{
    return "tgl transaksi,nomor member,nomor struk,nominal transaksi,jenis transaksi,branch code\n";
}

function bulkCsvRow(
    Member $member,
    TransactionType $transactionType,
    string $receiptNumber,
    string $purchaseNominal,
    ?string $transactionDate = null,
): string {
    $date = $transactionDate ?? Carbon::today()->toDateString();

    return implode(',', [
        $date,
        $member->member_number,
        $receiptNumber,
        $purchaseNominal,
        $transactionType->type_key,
        '',
    ])."\n";
}

function dispatchBulkJob(PointInjectionBatch $batch): void
{
    ProcessBulkInjectionJob::dispatchSync($batch);
}

it('validates a successful bulk row with calculated points', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-BULK-001',
        '1500000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $batch->refresh();

    expect($batch->total_rows)->toBe(1)
        ->and($batch->successful_rows)->toBe(1)
        ->and($batch->failed_rows)->toBe(0);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail)->not->toBeNull()
        ->and($detail?->status)->toBe(InjectionStatus::Validated)
        ->and($detail?->calculated_points)->toBe(15)
        ->and($detail?->error_message)->toBeNull();
});

it('marks unknown members as failed', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().implode(',', [
        Carbon::today()->toDateString(),
        'UNKNOWN-MEMBER-999',
        'RCP-BULK-002',
        '100000',
        $fixtures['transactionType']->type_key,
        '',
    ])."\n";
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Failed)
        ->and($detail?->error_message)->toBe('Member tidak ditemukan');
});

it('marks suspended members as failed', function (): void {
    $fixtures = createBulkInjectionFixtures(isSuspended: true);
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-BULK-003',
        '100000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Failed)
        ->and($detail?->error_message)->toBe('Member dinonaktifkan');
});

it('requires receipt number in bulk rows', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().implode(',', [
        Carbon::today()->toDateString(),
        $fixtures['member']->member_number,
        '',
        '100000',
        $fixtures['transactionType']->type_key,
        '',
    ])."\n";
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Failed)
        ->and($detail?->error_message)->toBe('Nomor struk wajib diisi');
});

it('marks duplicate receipts within the same file as failed', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader()
        .bulkCsvRow($fixtures['member'], $fixtures['transactionType'], 'RCP-DUP-001', '100000')
        .bulkCsvRow($fixtures['member'], $fixtures['transactionType'], 'RCP-DUP-001', '200000');
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $details = PointInjectionDetail::query()
        ->where('batch_id', $batch->id)
        ->orderBy('row_number')
        ->get();

    expect($details)->toHaveCount(2)
        ->and($details[0]->status)->toBe(InjectionStatus::Validated)
        ->and($details[1]->status)->toBe(InjectionStatus::Failed)
        ->and($details[1]->error_message)->toBe('Nomor struk duplikat dalam file');
});

it('validates rows below conversion minimum with zero points', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-BULK-004',
        '50000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Validated)
        ->and($detail?->calculated_points)->toBe(0);
});

it('rejects future transaction dates', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-BULK-005',
        '100000',
        Carbon::tomorrow()->toDateString(),
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Failed)
        ->and($detail?->error_message)->toBe('Tanggal transaksi tidak boleh melebihi hari ini');
});
