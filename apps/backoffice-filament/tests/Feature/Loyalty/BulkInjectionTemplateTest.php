<?php

declare(strict_types=1);

use App\Enums\InjectionStatus;
use App\Enums\TierStatus;
use App\Filament\Resources\PointInjectionBatches\Actions\DownloadBulkTemplateAction;
use App\Jobs\ProcessBulkInjectionJob;
use App\Models\Branch;
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
use PhpOffice\PhpSpreadsheet\IOFactory;

uses(DatabaseTransactions::class);

it('has bulk injection template xlsx with expected headers', function (): void {
    $path = storage_path(DownloadBulkTemplateAction::TEMPLATE_PATH);

    expect(file_exists($path))->toBeTrue();

    $sheet = IOFactory::load($path)->getActiveSheet();
    $header = [];

    foreach (range('A', 'F') as $column) {
        $header[] = (string) $sheet->getCell($column.'1')->getValue();
    }

    expect($header)->toBe([
        'tgl_transaksi',
        'nomor_member',
        'nomor_struk',
        'nominal_transaksi',
        'jenis_transaksi',
        'branch_code',
    ]);

    expect((string) $sheet->getCell('A2')->getValue())->toBe('15-06-2026');
});

it('validates template-format sample row when demo member exists', function (): void {
    (new TransactionTypeSeeder)->run();
    (new TierMemberSeeder)->run();

    $transactionType = TransactionType::query()->where('type_key', 'PERHIASAN')->firstOrFail();
    $tierMember = TierMember::query()->where('tier_code', TierStatus::Silver)->firstOrFail();

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
        'member_number' => 'HKD0000001',
        'current_tier' => TierStatus::Silver,
        'point_balance' => 0,
        'highest_point' => 0,
        'is_suspended' => false,
    ]);

    if (! Branch::query()->where('branch_code', 'HK01')->exists()) {
        Branch::factory()->create([
            'branch_code' => 'HK01',
            'address_id' => null,
        ]);
    }

    $csv = bulkCsvHeader().implode(',', [
        '15-06-2026',
        $member->member_number,
        'CONTOH-TPL-'.uniqid(),
        '1500000',
        $transactionType->type_key,
        'HK01',
    ])."\n";

    $batch = createBulkInjectionBatchFromCsv($csv);
    ProcessBulkInjectionJob::dispatchSync($batch);

    $detail = PointInjectionDetail::query()->where('batch_id', $batch->id)->first();

    expect($detail)->not->toBeNull()
        ->and($detail?->status)->toBe(InjectionStatus::Validated)
        ->and($detail?->error_message)->toBeNull()
        ->and($detail?->transaction_date?->format('Y-m-d'))->toBe('2026-06-15');
});
