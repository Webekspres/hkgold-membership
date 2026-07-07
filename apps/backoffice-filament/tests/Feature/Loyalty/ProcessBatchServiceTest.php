<?php

declare(strict_types=1);

use App\Enums\InjectionStatus;
use App\Enums\Role;
use App\Exceptions\Loyalty\ProcessBatchException;
use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use App\Models\PointMutation;
use App\Models\User;
use App\Services\Loyalty\ProcessBatchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;

uses(DatabaseTransactions::class);

/**
 * @return array{batch: PointInjectionBatch, actor: User, member: Member}
 */
function createValidatedBatchForProcess(int $rowCount = 1): array
{
    $fixtures = createBulkInjectionFixtures();
    $actor = User::factory()->create(['role' => Role::Administrator]);

    $rows = '';
    for ($i = 1; $i <= $rowCount; $i++) {
        $rows .= bulkCsvRow(
            $fixtures['member'],
            $fixtures['transactionType'],
            'RCP-PROC-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            '1500000',
        );
    }

    $batch = createBulkInjectionBatchFromCsv(bulkCsvHeader().$rows);
    dispatchBulkJob($batch);
    $batch->refresh();

    return [
        'batch' => $batch,
        'actor' => $actor,
        'member' => $fixtures['member'],
        'transactionType' => $fixtures['transactionType'],
    ];
}

it('processes validated batch into point mutations and resolves batch', function (): void {
    $data = createValidatedBatchForProcess();
    $service = app(ProcessBatchService::class);

    $result = $service->process($data['batch'], $data['actor'], '127.0.0.1');

    expect($result->rowsProcessed)->toBe(1)
        ->and($result->totalPointsInjected)->toBe(15)
        ->and($result->uniqueMembers)->toBe(1);

    $data['batch']->refresh();
    $data['member']->refresh();

    expect($data['batch']->resolved)->toBeTrue()
        ->and($data['batch']->total_points_injected)->toBe(15)
        ->and($data['member']->point_balance)->toBe(515);

    $detail = PointInjectionDetail::query()->where('batch_id', $data['batch']->id)->first();
    expect($detail?->status)->toBe(InjectionStatus::Success);

    $mutation = PointMutation::query()->where('source_id', $data['batch']->id)->first();
    expect($mutation)->not->toBeNull()
        ->and($mutation?->points_issued)->toBe(15)
        ->and($mutation?->balance_snapshot)->toBe(515);

    $log = ActivityLog::query()
        ->where('auditable_type', 'PointInjectionBatch')
        ->where('auditable_id', $data['batch']->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log?->action)->toBe('bulk_point_injection')
        ->and($log?->after_json['resolved'])->toBeTrue();
});

it('creates zero-point mutation without changing member balance', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $actor = User::factory()->create(['role' => Role::Administrator]);
    $csv = bulkCsvHeader().bulkCsvRow(
        $fixtures['member'],
        $fixtures['transactionType'],
        'RCP-PROC-ZERO',
        '50000',
    );
    $batch = createBulkInjectionBatchFromCsv($csv);
    dispatchBulkJob($batch);
    $batch->refresh();

    $previousBalance = (int) $fixtures['member']->point_balance;

    app(ProcessBatchService::class)->process($batch, $actor, '127.0.0.1');

    $fixtures['member']->refresh();
    expect($fixtures['member']->point_balance)->toBe($previousBalance);

    $mutation = PointMutation::query()->where('source_id', $batch->id)->first();
    expect($mutation?->points_issued)->toBe(0)
        ->and($mutation?->balance_snapshot)->toBe($previousBalance);
});

it('accumulates balance snapshot for same member across multiple rows', function (): void {
    $data = createValidatedBatchForProcess(rowCount: 2);
    $previousBalance = (int) $data['member']->point_balance;

    app(ProcessBatchService::class)->process($data['batch'], $data['actor'], '127.0.0.1');

    $mutations = PointMutation::query()
        ->where('source_id', $data['batch']->id)
        ->orderBy('uploaded_at')
        ->get();

    expect($mutations)->toHaveCount(2)
        ->and($mutations[0]->balance_snapshot)->toBe($previousBalance + 15)
        ->and($mutations[1]->balance_snapshot)->toBe($previousBalance + 30);

    $data['member']->refresh();
    expect($data['member']->point_balance)->toBe($previousBalance + 30);
});

it('rolls back when member is suspended during process', function (): void {
    $data = createValidatedBatchForProcess();
    $data['member']->update(['is_suspended' => true]);

    expect(fn () => app(ProcessBatchService::class)->process($data['batch'], $data['actor'], '127.0.0.1'))
        ->toThrow(ProcessBatchException::class, 'ditangguhkan');

    $data['batch']->refresh();
    expect($data['batch']->resolved)->toBeFalse()
        ->and(PointMutation::query()->where('source_id', $data['batch']->id)->count())->toBe(0);
});

it('rolls back on duplicate receipt in point mutations', function (): void {
    $data = createValidatedBatchForProcess();
    $detail = PointInjectionDetail::query()->where('batch_id', $data['batch']->id)->firstOrFail();

    PointMutation::query()->create([
        'member_id' => $data['member']->id,
        'branch_id' => null,
        'receipt_number' => $detail->receipt_number,
        'transaction_type_id' => $detail->transaction_type_id,
        'purchase_nominal' => $detail->purchase_nominal,
        'points_issued' => 1,
        'points_redeemed' => 0,
        'balance_snapshot' => 1,
        'transaction_date' => Carbon::today(),
        'uploaded_at' => now(),
    ]);

    expect(fn () => app(ProcessBatchService::class)->process($data['batch'], $data['actor'], '127.0.0.1'))
        ->toThrow(ProcessBatchException::class, 'sudah digunakan');

    $data['batch']->refresh();
    expect($data['batch']->resolved)->toBeFalse();
});

it('rejects already resolved batch', function (): void {
    $data = createValidatedBatchForProcess();
    $service = app(ProcessBatchService::class);
    $service->process($data['batch'], $data['actor'], '127.0.0.1');

    $data['batch']->refresh();

    expect(fn () => $service->assertBatchCanProcess($data['batch']))
        ->toThrow(ProcessBatchException::class, 'sudah pernah diproses');
});

it('rejects batch with failed rows', function (): void {
    $fixtures = createBulkInjectionFixtures();
    $csv = bulkCsvHeader()
        .bulkCsvRow($fixtures['member'], $fixtures['transactionType'], 'RCP-OK-001', '1500000')
        .implode(',', [
            Carbon::today()->format('d-m-Y'),
            'UNKNOWN-MEMBER',
            'RCP-FAIL-001',
            '100000',
            $fixtures['transactionType']->type_key,
            '',
        ])."\n";
    $batch = createBulkInjectionBatchFromCsv($csv);
    dispatchBulkJob($batch);
    $batch->refresh();

    expect(fn () => app(ProcessBatchService::class)->assertBatchCanProcess($batch))
        ->toThrow(ProcessBatchException::class, 'gagal');
});

it('builds process summary from validated details', function (): void {
    $data = createValidatedBatchForProcess();
    $summary = app(ProcessBatchService::class)->buildSummary($data['batch']);

    expect($summary->totalRows)->toBe(1)
        ->and($summary->uniqueMembers)->toBe(1)
        ->and($summary->totalPoints)->toBe(15);
});
