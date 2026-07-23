<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Pages;

use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Filament\Resources\RedeemTokens\Actions\VerifyRedeemTokenAction;
use App\Filament\Resources\RedeemTokens\RedeemTokenResource;
use App\Filament\Resources\RedeemTokens\Support\VerifyRedeemTokenFormSupport;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRedeemTokens extends ListRecords
{
    protected static string $resource = RedeemTokenResource::class;

    protected function getHeaderActions(): array
    {
        if (! Auth::user()?->can('Update:RedeemToken')) {
            return [];
        }

        return [
            VerifyRedeemTokenAction::make(),
        ];
    }

    public function resendRedeemOtp(string $tokenCode): void
    {
        $normalized = VerifyRedeemTokenFormSupport::normalizeTokenCode($tokenCode);
        if ($normalized === null) {
            Notification::make()
                ->title('Token tidak valid')
                ->body('Format kode token tidak valid.')
                ->danger()
                ->send();

            return;
        }

        $token = VerifyRedeemTokenFormSupport::findAvailableToken($normalized);
        if ($token === null || $token->member === null) {
            Notification::make()
                ->title('Token tidak valid')
                ->body('Token tidak tersedia saat mengirim ulang OTP.')
                ->danger()
                ->send();

            return;
        }

        try {
            VerifyRedeemTokenFormSupport::sendRedeemOtp($token);
        } catch (RedeemConfirmationException $exception) {
            Notification::make()
                ->title('Gagal mengirim OTP')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('OTP terkirim ulang')
            ->body('Kode OTP baru telah dikirim ke WhatsApp member.')
            ->success()
            ->send();
    }
}
