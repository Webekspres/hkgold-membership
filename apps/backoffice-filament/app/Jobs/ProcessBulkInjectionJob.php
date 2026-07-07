<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\InjectionStatus;
use App\Filament\Resources\PointInjectionBatches\Support\BulkInjectionUploadSupport;
use App\Imports\PointInjectionImport;
use App\Models\PointInjectionBatch;
use App\Models\PointInjectionDetail;
use App\Services\Loyalty\BulkInjectionRowValidator;
use App\Services\Loyalty\RecalculateDetailPointsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessBulkInjectionJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    private const COUNTER_FLUSH_INTERVAL = 50;

    public function __construct(public PointInjectionBatch $batch)
    {
        $this->onQueue('bulk-injection');
    }

    public function handle(
        BulkInjectionUploadSupport $uploadSupport,
        BulkInjectionRowValidator $validator,
        RecalculateDetailPointsService $recalculate,
    ): void {
        $batch = $this->batch->fresh(['media']);

        if ($batch === null) {
            return;
        }

        $batch->import_started_at = now();
        $batch->save();

        $media = $batch->media;

        if ($media === null) {
            Log::error('ProcessBulkInjectionJob: media tidak ditemukan.', ['batch_id' => $batch->id]);
            $batch->import_started_at = null;
            $batch->save();

            return;
        }

        $tempPath = $uploadSupport->downloadToTempFile($media);

        try {
            $import = new PointInjectionImport;
            Excel::import($import, $tempPath);
            $rows = $import->getRows();

            $batch->total_rows = count($rows);
            $batch->successful_rows = 0;
            $batch->failed_rows = 0;
            $batch->save();

            $seenReceipts = [];

            foreach ($rows as $index => $row) {
                $result = $validator->validate($row, $seenReceipts);

                $detail = PointInjectionDetail::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $row['row_number'],
                    'raw_member_number' => trim((string) ($row['raw_member_number'] ?? '')),
                    'raw_branch_code' => $result->rawBranchCode(),
                    'purchase_nominal' => $result->purchaseNominal(),
                    'transaction_type_id' => $result->transactionTypeId(),
                    'transaction_date' => $result->transactionDate(),
                    'calculated_points' => 0,
                    'status' => $result->isValid() ? InjectionStatus::Validated : InjectionStatus::Failed,
                    'error_message' => $result->errorMessage(),
                    'receipt_number' => $result->receiptNumber(),
                ]);

                if ($result->isValid()) {
                    $recalculate->recalculate($detail);
                    $batch->successful_rows++;
                } else {
                    $batch->failed_rows++;
                }

                if (($index + 1) % self::COUNTER_FLUSH_INTERVAL === 0) {
                    $batch->save();
                }
            }

            $batch->save();
            $batch->import_started_at = null;
            $batch->save();
        } finally {
            $uploadSupport->deleteTempFile($tempPath);
        }
    }

    public function failed(?Throwable $exception): void
    {
        PointInjectionBatch::query()
            ->whereKey($this->batch->id)
            ->update(['import_started_at' => null]);

        Log::error('ProcessBulkInjectionJob gagal.', [
            'batch_id' => $this->batch->id,
            'message' => $exception?->getMessage(),
        ]);
    }
}
