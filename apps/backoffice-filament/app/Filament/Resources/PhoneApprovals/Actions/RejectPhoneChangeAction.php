<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhoneApprovals\Actions;

use App\Enums\ApprovalStatus;
use App\Exceptions\ChangePhone\ChangePhoneApprovalException;
use App\Models\PhoneApproval;
use App\Models\User;
use App\Services\ChangePhone\ChangePhoneApiClient;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RejectPhoneChangeAction
{
    public static function make(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->color('danger')
            ->icon('heroicon-o-x-mark')
            ->requiresConfirmation()
            ->modalHeading('Tolak ganti nomor HP?')
            ->form([
                Textarea::make('action_notes')
                    ->label('Alasan penolakan')
                    ->required()
                    ->rows(3),
            ])
            ->visible(fn (PhoneApproval $record): bool => $record->status === ApprovalStatus::Pending)
            ->action(function (PhoneApproval $record, array $data): void {
                $staffId = self::requireStaffId();

                try {
                    app(ChangePhoneApiClient::class)->reject(
                        $record->id,
                        $staffId,
                        (string) $data['action_notes'],
                    );
                } catch (ChangePhoneApprovalException $e) {
                    Notification::make()
                        ->title('Gagal menolak')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Permintaan ditolak')
                    ->success()
                    ->send();

                $record->refresh();
            });
    }

    private static function requireStaffId(): int
    {
        /** @var User|null $user */
        $user = Auth::user();
        $staffId = $user?->staff?->id;

        if ($staffId === null) {
            throw ChangePhoneApprovalException::failed('Akun admin belum terhubung ke data staff.');
        }

        return (int) $staffId;
    }
}
