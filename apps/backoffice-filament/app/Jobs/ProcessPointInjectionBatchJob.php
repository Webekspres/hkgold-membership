<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PointInjectionBatch;
use App\Models\User;
use App\Services\Loyalty\ProcessBatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPointInjectionBatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public PointInjectionBatch $batch,
        public User $actor,
        public string $ipAddress,
    ) {
        $this->onQueue('bulk-injection');
    }

    public function handle(ProcessBatchService $service): void
    {
        $service->process($this->batch, $this->actor, $this->ipAddress);
    }

    public function failed(?Throwable $exception): void
    {
        PointInjectionBatch::query()
            ->whereKey($this->batch->id)
            ->update(['processing_started_at' => null]);

        Log::error('ProcessPointInjectionBatchJob gagal.', [
            'batch_id' => $this->batch->id,
            'message' => $exception?->getMessage(),
        ]);
    }
}
