<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;

class ArchivePointsAction
{
    public static function make(): Action
    {
        return Action::make('archivePoints')
            ->label('Arsipkan Poin')
            ->icon('heroicon-o-archive-box')
            ->color('warning')
            ->modalHeading('Arsipkan Poin Tahunan')
            ->modalDescription('Apakah Anda yakin ingin menjalankan proses pengarsipan poin tahunan secara masal? Tindakan ini tidak dapat dibatalkan.')
            ->modalSubmitActionLabel('Jalankan')
            ->form([
                Placeholder::make('info')
                    ->content('Ini adalah aksi simulasi/dummy untuk pengarsipan poin tahunan.'),
            ])
            ->action(function (): void {
                Notification::make()
                    ->title('Pengarsipan poin berhasil dijalankan (Simulasi)')
                    ->success()
                    ->send();
            });
    }
}
