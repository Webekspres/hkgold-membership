<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Support\R2BucketCleaner;
use Illuminate\Database\Events\DatabaseRefreshed;

class WipeR2BucketOnDatabaseRefreshed
{
    public function __construct(
        private readonly R2BucketCleaner $r2BucketCleaner,
    ) {}

    public function handle(DatabaseRefreshed $event): void
    {
        if (! $event->seeding) {
            return;
        }

        $this->r2BucketCleaner->wipe();
    }
}
