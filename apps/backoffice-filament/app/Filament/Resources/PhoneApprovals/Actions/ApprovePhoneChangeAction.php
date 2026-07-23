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

class ApprovePhoneChangeAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Setujui')
            ->color('success')
            ->icon('heroicon-o-check')
            ->requiresConfirmation()
            ->modalHeading('Setujui ganti nomor HP?')
            ->modalDescription('Nomor member akan diganti ke nomor baru. Pastikan identitas sudah diverifikasi di luar sistem.')
            ->form([
                Textarea::make('action_notes')
                    ->label('Catatan (opsional)')
                    ->rows(3),
            ])
            ->visible(fn (PhoneApproval $record): bool => $record->status === ApprovalStatus::Pending)
            ->action(function (PhoneApproval $record, array $data): void {
                $staffId = self::requireStaffId();

                try {
                    app(ChangePhoneApiClient::class)->approve(
                        $record->id,
                        $staffId,
                        isset($data['action_notes']) ? (string) $data['action_notes'] : null,
                    );
                } catch (ChangePhoneApprovalException $e) {
                    Notification::make()
                        ->title('Gagal menyetujui')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Permintaan disetujui')
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
