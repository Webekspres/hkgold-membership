<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\R2BucketCleaner;
use Illuminate\Console\Command;
use Throwable;

class WipeR2BucketCommand extends Command
{
    protected $signature = 'r2:wipe {--force : Wipe even when R2_WIPE_ON_FRESH_SEED is disabled}';

    protected $description = 'Delete all objects from the configured Cloudflare R2 bucket';

    public function handle(R2BucketCleaner $r2BucketCleaner): int
    {
        if (! $this->option('force') && ! $r2BucketCleaner->isEnabled()) {
            $this->components->warn('R2 wipe is disabled. Set R2_WIPE_ON_FRESH_SEED=true or use --force.');

            return self::FAILURE;
        }

        $this->info('Bucket: '.(string) config('filesystems.disks.r2.bucket'));

        try {
            $deletedCount = $r2BucketCleaner->wipe(force: (bool) $this->option('force'));

            if ($deletedCount === 0) {
                $this->components->info('R2 bucket is already empty.');

                return self::SUCCESS;
            }

            $this->components->info("Deleted {$deletedCount} object(s) from R2 bucket.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error('R2 wipe failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
