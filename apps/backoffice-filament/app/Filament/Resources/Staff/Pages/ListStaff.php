<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Contracts\View\View;

class ListStaff extends ListRecords
{
    protected static bool $roleFilterHookRegistered = false;

    protected static string $resource = StaffResource::class;

    public function boot(): void
    {
        if (static::$roleFilterHookRegistered) {
            return;
        }

        static::$roleFilterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn (): View => view('filament.resources.staff.partials.role-filter'),
            static::class,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Staff')
                ->goldStyle(),
        ];
    }
}
