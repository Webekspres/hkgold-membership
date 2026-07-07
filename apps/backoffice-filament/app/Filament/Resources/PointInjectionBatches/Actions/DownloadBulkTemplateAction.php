<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointInjectionBatches\Actions;

use Filament\Actions\Action;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadBulkTemplateAction
{
    public const TEMPLATE_PATH = 'app/templates/template-injeksi-poin.xlsx';

    public const DOWNLOAD_NAME = 'template-injeksi-poin.xlsx';

    public static function make(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Download Template')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(fn (): BinaryFileResponse => response()->download(
                storage_path(self::TEMPLATE_PATH),
                self::DOWNLOAD_NAME,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ));
    }
}
