<?php

namespace App\Providers;

use App\Listeners\WipeR2BucketOnDatabaseRefreshed;
use Illuminate\Database\Events\DatabaseRefreshed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(DatabaseRefreshed::class, WipeR2BucketOnDatabaseRefreshed::class);
    }
}
