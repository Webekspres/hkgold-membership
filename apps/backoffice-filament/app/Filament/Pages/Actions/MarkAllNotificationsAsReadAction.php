<?php

declare(strict_types=1);

namespace App\Filament\Pages\Actions;

use App\Services\Notification\NotificationDispatcher;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MarkAllNotificationsAsReadAction
{
    public static function make(): Action
    {
        return Action::make('tandaiSemuaDibaca')
            ->label('Tandai Semua Dibaca')
            ->icon('heroicon-o-check-circle')
            ->color('gray')
            ->disabled(function (): bool {
                $user = Auth::user();

                if ($user === null) {
                    return true;
                }

                return app(NotificationDispatcher::class)->unreadCountForUser($user) === 0;
            })
            ->action(function (): void {
                $user = Auth::user();

                if ($user === null) {
                    return;
                }

                $updated = app(NotificationDispatcher::class)->markAllAsReadForUser($user);

                Notification::make()
                    ->title('Semua notifikasi ditandai dibaca')
                    ->body($updated > 0
                        ? "{$updated} notifikasi diperbarui."
                        : 'Tidak ada notifikasi yang perlu ditandai dibaca.')
                    ->success()
                    ->send();
            });
    }
}
