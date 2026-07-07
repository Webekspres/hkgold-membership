<?php

declare(strict_types=1);

use App\Enums\TierStatus;
use App\Jobs\ProcessBulkInjectionJob;
use App\Models\ConversionRule;
use App\Models\Media;
use App\Models\Member;
use App\Models\PointInjectionBatch;
use App\Models\TierMember;
use App\Models\TransactionType;
use Database\Seeders\TierMemberSeeder;
use Database\Seeders\TransactionTypeSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
    $date = $transactionDate ?? Carbon::today()->format('d-m-Y');

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
