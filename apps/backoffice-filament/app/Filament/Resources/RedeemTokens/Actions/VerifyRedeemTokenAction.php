<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Actions;

use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Filament\Resources\RedeemInvoices\RedeemInvoiceResource;
use App\Filament\Resources\RedeemTokens\Support\VerifyRedeemTokenFormSupport;
use App\Services\Redeem\FonnteOtpClient;
use App\Services\Redeem\RedeemConfirmationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class VerifyRedeemTokenAction
{
    public static function make(): Action
    {
        return Action::make('verifyRedeemToken')
            ->label('Verifikasi & Scan Token')
            ->icon('heroicon-o-qr-code')
            ->color('primary')
            ->goldStyle()
            ->modalWidth(Width::TwoExtraLarge)
            ->modalHeading('Verifikasi & Konfirmasi Redeem')
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('Konfirmasi Selesai')
            ->steps([
                Step::make('Token')
                    ->description('Masukkan kode token dari aplikasi member')
                    ->schema([
                        TextInput::make('token_code')
                            ->label('Kode Token')
                            ->required()
                            ->length(10)
                            ->maxLength(10)
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn (?string $state): string => strtoupper(trim((string) $state))),
                    ])
                    ->afterValidation(function (Get $get): void {
                        $tokenCode = strtoupper(trim((string) $get('token_code')));
                        $token = VerifyRedeemTokenFormSupport::findAvailableToken($tokenCode);

                        if ($token === null) {
                            Notification::make()
                                ->title('Token tidak valid')
                                ->body('Token tidak ditemukan, sudah dipakai, atau kedaluwarsa.')
                                ->danger()
                                ->send();

                            throw new Halt;
                        }

                        $user = Auth::user();
                        if (
                            $user !== null
                            && $user->role === Role::StoreManager
                            && $user->staff?->branch_id !== $token->branch_id
                        ) {
                            Notification::make()
                                ->title('Cabang tidak cocok')
                                ->body('Token redeem tidak untuk cabang Anda.')
                                ->danger()
                                ->send();

                            throw new Halt;
                        }
                    }),
                Step::make('Kirim OTP')
                    ->description('Periksa detail member lalu kirim OTP WhatsApp')
                    ->schema([
                        Placeholder::make('preview')
                            ->label('Detail kupon')
                            ->content(fn (Get $get): HtmlString => new HtmlString(
                                VerifyRedeemTokenFormSupport::buildPreviewHtml([
                                    'token_code' => $get('token_code'),
                                ]),
                            )),
                    ])
                    ->afterValidation(function (Get $get): void {
                        $tokenCode = strtoupper(trim((string) $get('token_code')));
                        $token = VerifyRedeemTokenFormSupport::findAvailableToken($tokenCode);

                        if ($token === null || $token->member === null) {
                            Notification::make()
                                ->title('Token tidak valid')
                                ->body('Token tidak tersedia saat mengirim OTP.')
                                ->danger()
                                ->send();

                            throw new Halt;
                        }

                        try {
                            app(FonnteOtpClient::class)->send(
                                (string) $token->member->phone_number,
                                $tokenCode,
                            );
                        } catch (RedeemConfirmationException $exception) {
                            Notification::make()
                                ->title('Gagal mengirim OTP')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();

                            throw new Halt;
                        }

                        Notification::make()
                            ->title('OTP terkirim')
                            ->body('Kode OTP telah dikirim ke WhatsApp member.')
                            ->success()
                            ->send();
                    }),
                Step::make('Konfirmasi')
                    ->description('Masukkan OTP yang disebutkan member')
                    ->schema([
                        TextInput::make('otp')
                            ->label('Kode OTP')
                            ->required()
                            ->length(6)
                            ->numeric()
                            ->placeholder('6 digit'),
                    ]),
            ])
            ->action(function (array $data, RedeemConfirmationService $service, Action $action): void {
                $user = Auth::user();
                if ($user === null) {
                    Notification::make()
                        ->title('Konfirmasi gagal')
                        ->body('Sesi login tidak valid.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $result = $service->confirm(
                        strtoupper(trim((string) ($data['token_code'] ?? ''))),
                        (string) ($data['otp'] ?? ''),
                        $user,
                        request()->ip() ?? '127.0.0.1',
                    );
                } catch (RedeemConfirmationException $exception) {
                    Notification::make()
                        ->title('Konfirmasi gagal')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Redeem berhasil')
                    ->body(sprintf(
                        'Invoice %s — %s poin untuk %s (%s).',
                        $result->invoiceNumber,
                        number_format($result->pointsRedeemed, 0, ',', '.'),
                        $result->memberName,
                        $result->rewardName,
                    ))
                    ->success()
                    ->send();

                $action->redirect(RedeemInvoiceResource::getUrl('view', ['record' => $result->invoiceId]));
            });
    }
}
