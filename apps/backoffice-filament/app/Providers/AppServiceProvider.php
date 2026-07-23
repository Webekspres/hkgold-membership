<?php

namespace App\Providers;

use App\Listeners\WipeR2BucketOnDatabaseRefreshed;
use Filament\Actions\Action;
use Illuminate\Database\Events\DatabaseRefreshed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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

        // Staging/prod di balik TLS terminator: paksa https untuk asset/route URL
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Action::macro('goldStyle', function () {
            return $this
                ->extraAttributes([
                    'style' => 'background: linear-gradient(135deg, #D1A13B, #ebca86, #9A6B1F); border: none; border-radius: 6px; box-shadow: 0 2px 6px rgb(154 107 31 / 0.25); font-weight: 600; color: #292524;',
                    'class' => 'hover:brightness-110 hover:scale-105 active:brightness-95 active:scale-95 focus-visible:ring-2 focus-visible:ring-primary-600 focus-visible:ring-offset-2 transition-all duration-300',
                ]);
        });
    }
}
