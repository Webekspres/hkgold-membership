<?php

declare(strict_types=1);

use App\Enums\InjectionStatus;
use App\Models\PointInjectionDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

uses(DatabaseTransactions::class);

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
        Carbon::today()->format('d-m-Y'),
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
        Carbon::today()->format('d-m-Y'),
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

it('parses d-m-Y transaction dates from uploaded rows', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-BULK-DMY',
        '1500000',
        '31-12-2025',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Validated)
        ->and($detail?->transaction_date?->format('Y-m-d'))->toBe('2025-12-31');
});

it('rejects future transaction dates', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-BULK-005',
        '100000',
        Carbon::tomorrow()->format('d-m-Y'),
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail?->status)->toBe(InjectionStatus::Failed)
        ->and($detail?->error_message)->toBe('Tanggal transaksi tidak boleh melebihi hari ini');
});
