<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Pages;

use App\Filament\Resources\PointInjectionBatches\Actions\DownloadBulkTemplateAction;
use App\Filament\Resources\PointInjectionBatches\Actions\UploadBulkAction;
use App\Filament\Resources\PointInjectionBatches\PointInjectionBatchResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Contracts\View\View;

class ListPointInjectionBatches extends ListRecords
{
    protected static bool $resolvedFilterHookRegistered = false;

    protected static string $resource = PointInjectionBatchResource::class;

    public function boot(): void
    {
        if (static::$resolvedFilterHookRegistered) {
            return;
        }

        static::$resolvedFilterHookRegistered = true;

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_START,
            fn (): View => view('filament.resources.point-injection-batches.partials.resolved-filter'),
            static::class,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DownloadBulkTemplateAction::make(),
            UploadBulkAction::make(),
        ];
    }
}
