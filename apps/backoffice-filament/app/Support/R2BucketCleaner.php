<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class R2BucketCleaner
{
    public function wipe(bool $force = false): int
    {
        if (! $force && ! $this->isEnabled()) {
            return 0;
        }

        if (blank(config('filesystems.disks.r2.bucket'))) {
            return 0;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('r2');

        try {
            $files = $disk->allFiles();

            if ($files === []) {
                return 0;
            }

            foreach (array_chunk($files, 1000) as $chunk) {
                $disk->delete($chunk);
            }

            Log::info('R2 bucket wiped after database refresh.', [
                'bucket' => config('filesystems.disks.r2.bucket'),
                'deleted_count' => count($files),
            ]);

            return count($files);
        } catch (Throwable $exception) {
            Log::warning('Failed to wipe R2 bucket after database refresh.', [
                'bucket' => config('filesystems.disks.r2.bucket'),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function isEnabled(): bool
    {
        return (bool) config('filesystems.wipe_r2_on_fresh_seed', false);
    }
}
