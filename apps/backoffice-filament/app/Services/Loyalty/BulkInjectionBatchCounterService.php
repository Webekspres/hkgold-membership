<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Enums\InjectionStatus;
use App\Models\PointInjectionBatch;

class BulkInjectionBatchCounterService
{
    public function syncFromDetails(PointInjectionBatch $batch): void
    {
        $batch->update([
            'total_rows' => $batch->details()->count(),
            'successful_rows' => $batch->details()->where('status', InjectionStatus::Validated)->count(),
            'failed_rows' => $batch->details()->where('status', InjectionStatus::Failed)->count(),
        ]);
    }
}
