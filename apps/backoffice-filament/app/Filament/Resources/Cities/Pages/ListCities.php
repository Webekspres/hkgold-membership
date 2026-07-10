<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Resources\Cities\CityResource;
use Filament\Support\Facades\FilamentView;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Contracts\View\View;

class ListCities extends ListRecords
{
    protected static bool $provinceFilterHookRegistered = false;

    protected static string $resource = CityResource::class;

    public function boot(): void
    {
        if (static::$provinceFilterHookRegistered) {
            return;
        }

        static::$provinceFilterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn (): View => view('filament.resources.cities.partials.province-filter'),
            static::class,
        );
    }
}
