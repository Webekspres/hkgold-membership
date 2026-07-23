<?php

declare(strict_types=1);

use App\Enums\InjectionStatus;
use App\Filament\Resources\PointInjectionBatches\Actions\RetryBulkImportAction;
use App\Jobs\ProcessBulkInjectionJob;
use App\Jobs\ProcessPointInjectionBatchJob;
use App\Models\PointInjectionDetail;
use App\Models\PointMutation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('processes batch via job and clears processing timestamp', function (): void {
    $data = createValidatedBatchForProcess();
    $data['batch']->update(['processing_started_at' => now()]);

    ProcessPointInjectionBatchJob::dispatchSync(
        $data['batch'],
        $data['actor'],
        '127.0.0.1',
    );

    $data['batch']->refresh();

    expect($data['batch']->resolved)->toBeTrue()
        ->and($data['batch']->processing_started_at)->toBeNull()
        ->and($data['batch']->total_points_injected)->toBe(15)
        ->and(PointMutation::query()->where('source_id', $data['batch']->id)->count())->toBe(1);
});

it('clears processing timestamp when job fails', function (): void {
    $data = createValidatedBatchForProcess();
    $data['member']->update(['is_suspended' => true]);
    $data['batch']->update(['processing_started_at' => now()]);

    try {
        ProcessPointInjectionBatchJob::dispatchSync(
            $data['batch'],
            $data['actor'],
            '127.0.0.1',
        );
    } catch (Throwable) {
        // expected
    }

    $job = new ProcessPointInjectionBatchJob($data['batch'], $data['actor'], '127.0.0.1');
    $job->failed(new RuntimeException('test failure'));

    $data['batch']->refresh();

    expect($data['batch']->processing_started_at)->toBeNull()
        ->and($data['batch']->resolved)->toBeFalse();
});

it('retries import by clearing details and resetting counters', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-RETRY-001',
        '1500000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);

    PointInjectionDetail::query()->create([
        'batch_id' => $batch->id,
        'row_number' => 1,
        'raw_member_number' => 'PARTIAL',
        'raw_branch_code' => '',
        'purchase_nominal' => '100000.00',
        'transaction_type_id' => $fixtures['transactionType']->id,
        'transaction_date' => now(),
        'calculated_points' => 0,
        'status' => InjectionStatus::Failed,
        'error_message' => 'partial',
        'receipt_number' => 'PARTIAL-001',
    ]);

    $batch->update([
        'total_rows' => 2,
        'successful_rows' => 0,
        'failed_rows' => 1,
    ]);

    expect(RetryBulkImportAction::canRetry($batch->fresh()))->toBeTrue();

    PointInjectionDetail::query()->where('batch_id', $batch->id)->delete();
    $batch->update([
        'total_rows' => 0,
        'successful_rows' => 0,
        'failed_rows' => 0,
        'import_started_at' => now(),
    ]);

    ProcessBulkInjectionJob::dispatchSync($batch->fresh());
    $batch->refresh();

    expect($batch->total_rows)->toBe(1)
        ->and($batch->import_started_at)->toBeNull()
        ->and(PointInjectionDetail::query()->where('batch_id', $batch->id)->count())->toBe(1);
});

it('clears import_started_at when bulk import job fails', function (): void {
    $batch = createBulkInjectionBatchFromCsv(bulkCsvHeader());
    $batch->update(['import_started_at' => now()]);

    $job = new ProcessBulkInjectionJob($batch);
    $job->failed(new RuntimeException('import failed'));

    $batch->refresh();
    expect($batch->import_started_at)->toBeNull();
});
