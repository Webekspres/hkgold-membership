<?php

namespace App\Providers;

use App\Listeners\WipeR2BucketOnDatabaseRefreshed;
use Filament\Actions\Action;
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

        Action::macro('goldStyle', function () {
            return $this
                ->extraAttributes([
                    'style' => 'background: linear-gradient(135deg, #f5c842, #e8a020); border: none; font-weight: 600; color: #111827;',
                    'class' => 'text-gray-900 hover:brightness-110 hover:scale-105 active:scale-95 transition-all duration-300',
                ]);
        });
    }
}
