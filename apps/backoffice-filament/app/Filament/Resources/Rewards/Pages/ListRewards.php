<?php

declare(strict_types=1);

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Rewards\RewardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Contracts\View\View;

class ListRewards extends ListRecords
{
    protected static bool $statusFilterHookRegistered = false;

    protected static string $resource = RewardResource::class;

    public function boot(): void
    {
        if (static::$statusFilterHookRegistered) {
            return;
        }

        static::$statusFilterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn (): View => view('filament.resources.rewards.partials.status-filter'),
            static::class,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah reward')
                ->goldStyle(),
        ];
    }
}
