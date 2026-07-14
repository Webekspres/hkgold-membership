<?php

declare(strict_types=1);

use App\Enums\InjectionStatus;
use App\Filament\Resources\PointInjectionBatches\Tables\PointInjectionDetailsTable;
use App\Models\PointInjectionDetail;
use App\Services\Loyalty\BulkInjectionBatchCounterService;
use App\Services\Loyalty\BulkInjectionRowValidator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

uses(DatabaseTransactions::class);

it('validates a failed detail after correcting member and receipt data', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().implode(',', [
        Carbon::today()->toDateString(),
        'UNKNOWN-MEMBER-999',
        'RCP-EDIT-001',
        '100000',
        $fixtures['transactionType']->type_key,
        '',
    ])."\n";
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->firstOrFail();

    expect($detail->status)->toBe(InjectionStatus::Failed);

    PointInjectionDetailsTable::applyDetailEdit($detail, [
        'raw_member_number' => $fixtures['member']->member_number,
        'receipt_number' => 'RCP-EDIT-001',
        'transaction_date' => Carbon::today()->toDateString(),
        'purchase_nominal' => 100000,
        'transaction_type_id' => $fixtures['transactionType']->id,
        'raw_branch_code' => null,
    ]);

    $detail->refresh();
    $batch->refresh();

    expect($detail->status)->toBe(InjectionStatus::Validated)
        ->and($detail->error_message)->toBeNull()
        ->and($batch->successful_rows)->toBe(1)
        ->and($batch->failed_rows)->toBe(0)
        ->and($batch->total_rows)->toBe(1);
});

it('marks a validated detail as failed when edited with invalid data', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-EDIT-002',
        '100000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->firstOrFail();

    expect($detail->status)->toBe(InjectionStatus::Validated);

    PointInjectionDetailsTable::applyDetailEdit($detail, [
        'raw_member_number' => 'UNKNOWN-MEMBER-999',
        'receipt_number' => 'RCP-EDIT-002',
        'transaction_date' => Carbon::today()->toDateString(),
        'purchase_nominal' => 100000,
        'transaction_type_id' => $fixtures['transactionType']->id,
        'raw_branch_code' => null,
    ]);

    $detail->refresh();
    $batch->refresh();

    expect($detail->status)->toBe(InjectionStatus::Failed)
        ->and($detail->error_message)->toBe('Member tidak ditemukan')
        ->and($batch->successful_rows)->toBe(0)
        ->and($batch->failed_rows)->toBe(1);
});

it('syncs batch counters after deleting a failed detail', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().implode(',', [
        Carbon::today()->toDateString(),
        'UNKNOWN-MEMBER-999',
        'RCP-DEL-001',
        '100000',
        $fixtures['transactionType']->type_key,
        '',
    ])."\n";
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->firstOrFail();

    expect($detail->status)->toBe(InjectionStatus::Failed);

    $detail->delete();

    app(BulkInjectionBatchCounterService::class)->syncFromDetails($batch->fresh());

    $batch->refresh();

    expect($batch->total_rows)->toBe(0)
        ->and($batch->failed_rows)->toBe(0)
        ->and($batch->successful_rows)->toBe(0)
        ->and(PointInjectionDetail::query()->where('batch_id', $batch->id)->count())->toBe(0);
});

it('detects duplicate receipts within the same batch on detail validation', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader()
        .bulkCsvRow($fixtures['member'], $fixtures['transactionType'], 'RCP-DUP-EDIT', '100000')
        .bulkCsvRow($fixtures['member'], $fixtures['transactionType'], 'RCP-DUP-OTHER', '200000');
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detailToEdit = PointInjectionDetail::query()
        ->where('batch_id', $batch->id)
        ->where('receipt_number', 'RCP-DUP-OTHER')
        ->firstOrFail();

    expect($detailToEdit->status)->toBe(InjectionStatus::Validated);

    PointInjectionDetailsTable::applyDetailEdit($detailToEdit, [
        'raw_member_number' => $fixtures['member']->member_number,
        'receipt_number' => 'RCP-DUP-EDIT',
        'transaction_date' => Carbon::today()->toDateString(),
        'purchase_nominal' => 200000,
        'transaction_type_id' => $fixtures['transactionType']->id,
        'raw_branch_code' => null,
    ]);

    $detailToEdit->refresh();

    expect($detailToEdit->status)->toBe(InjectionStatus::Failed)
        ->and($detailToEdit->error_message)->toBe('Nomor struk duplikat dalam file');
});

it('validates detail records through BulkInjectionRowValidator', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-VALIDATOR-001',
        '100000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    dispatchBulkJob($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->firstOrFail();

    $result = app(BulkInjectionRowValidator::class)->validateDetail($detail);

    expect($result->isValid())->toBeTrue()
        ->and($result->receiptNumber())->toBe('RCP-VALIDATOR-001');
});
