<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TestR2StorageCommand extends Command
{
    protected $signature = 'r2:test';

    protected $description = 'Test Cloudflare R2 read/write permissions used by the CMS';

    public function handle(): int
    {
        $disk = Storage::disk('r2');
        $testKey = 'content-banners/temp/r2-healthcheck-'.now()->format('YmdHis').'.txt';

        $this->info('Bucket: '.(string) config('filesystems.disks.r2.bucket'));
        $this->info('Endpoint: '.(string) config('filesystems.disks.r2.endpoint'));
        $this->info('Region: '.(string) config('filesystems.disks.r2.region'));
        $this->line('');

        try {
            $disk->put($testKey, 'r2-healthcheck');
            $this->components->info("Write OK: {$testKey}");

            $contents = $disk->get($testKey);
            $this->components->info('Read OK: '.$contents);

            $disk->delete($testKey);
            $this->components->info('Delete OK');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error('R2 test failed: '.$exception->getMessage());
            $this->newLine();
            $this->line('Periksa di Cloudflare Dashboard → R2 → Manage R2 API Tokens:');
            $this->line('  1. Buat token baru dengan permission "Object Read & Write"');
            $this->line('  2. Scope bucket: '.(string) config('filesystems.disks.r2.bucket'));
            $this->line('  3. Salin Access Key ID + Secret Access Key ke .env');
            $this->line('  4. Set CLOUDFLARE_R2_REGION=auto');
            $this->line('  5. Jalankan: php artisan config:clear && php artisan r2:test');

            return self::FAILURE;
        }
    }
}
