<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Loyalty\PointAnnualArchiveService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPointAnnualArchiveJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 900;

    public int $uniqueFor = 960;

    public function __construct(
        public User $actor,
        public string $ipAddress,
        public int $archiveYear,
    ) {
        $this->onQueue('bulk-injection');
    }

    public function uniqueId(): string
    {
        return 'point-annual-archive:'.$this->archiveYear;
    }

    public function handle(PointAnnualArchiveService $service): void
    {
        $service->markRunProcessing($this->actor, $this->archiveYear);

        try {
            $service->archive(
                actor: $this->actor,
                ipAddress: $this->ipAddress,
                archiveYear: $this->archiveYear,
            );
        } catch (Throwable $exception) {
            $service->markRunFailed(
                actor: $this->actor,
                archiveYear: $this->archiveYear,
                message: $exception->getMessage(),
            );

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        app(PointAnnualArchiveService::class)->markRunFailed(
            actor: $this->actor,
            archiveYear: $this->archiveYear,
            message: $exception?->getMessage() ?? 'Proses arsip poin gagal tanpa pesan error.',
        );

        Log::error('ProcessPointAnnualArchiveJob gagal.', [
            'archive_year' => $this->archiveYear,
            'actor_id' => $this->actor->id,
            'message' => $exception?->getMessage(),
        ]);
    }
}
