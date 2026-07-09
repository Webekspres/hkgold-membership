<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointAnnualArchives\Actions;

use App\Enums\Role;
use App\Exceptions\Loyalty\PointAnnualArchiveException;
use App\Jobs\ProcessPointAnnualArchiveJob;
use App\Models\User;
use App\Services\Loyalty\PointAnnualArchiveService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ArchivePointsAction
{
    public static function make(): Action
    {
        $targetYear = app(PointAnnualArchiveService::class)->resolveTargetYear();

        return Action::make('archivePoints')
            ->label('Arsipkan Poin')
            ->icon('heroicon-o-archive-box')
            ->color('warning')
            ->modalHeading('Arsipkan Poin Tahunan')
            ->modalDescription('Proses ini akan membuat snapshot seluruh member serta mereset saldo poin dan poin tertinggi aktif menjadi 0. Tindakan ini tidak dapat dibatalkan.')
            ->modalSubmitActionLabel('Jalankan')
            ->form([
                Placeholder::make('info')
                    ->content('Target tahun arsip: '.$targetYear.'. Proses akan berjalan di background.'),
            ])
            ->requiresConfirmation()
            ->visible(function () use ($targetYear): bool {
                /** @var User|null $actor */
                $actor = Auth::user();

                if ($actor === null || $actor->role !== Role::Administrator) {
                    return false;
                }

                if (! $actor->can('Create:PointAnnualArchivePeriod')) {
                    return false;
                }

                return app(PointAnnualArchiveService::class)->canArchiveYear($targetYear);
            })
            ->action(function (): void {
                /** @var User|null $actor */
                $actor = Auth::user();

                if ($actor === null) {
                    Notification::make()
                        ->title('Autentikasi gagal')
                        ->body('Silakan login ulang lalu coba lagi.')
                        ->danger()
                        ->send();

                    return;
                }

                $service = app(PointAnnualArchiveService::class);
                $targetYear = $service->resolveTargetYear();

                if ($actor->role !== Role::Administrator || ! $actor->can('Create:PointAnnualArchivePeriod')) {
                    Notification::make()
                        ->title('Akses ditolak')
                        ->body('Hanya administrator yang boleh menjalankan arsip poin.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    if (! $service->canArchiveYear($targetYear)) {
                        throw PointAnnualArchiveException::archiveAlreadyExists($targetYear);
                    }

                    $service->markRunQueued($actor, $targetYear);

                    ProcessPointAnnualArchiveJob::dispatch(
                        actor: $actor,
                        ipAddress: request()->ip() ?? '127.0.0.1',
                        archiveYear: $targetYear,
                    );

                    Notification::make()
                        ->title('Arsip poin sedang diproses')
                        ->body('Pengarsipan tahun '.$targetYear.' berjalan di background.')
                        ->success()
                        ->send();
                } catch (PointAnnualArchiveException $exception) {
                    Notification::make()
                        ->title('Arsip poin gagal dijalankan')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                } catch (Throwable) {
                    Notification::make()
                        ->title('Arsip poin gagal dijalankan')
                        ->body('Terjadi kesalahan saat mengantre proses arsip poin.')
                        ->danger()
                        ->send();
                }
            });
    }
}
