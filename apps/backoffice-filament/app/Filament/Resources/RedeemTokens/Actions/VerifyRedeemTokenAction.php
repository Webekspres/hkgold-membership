<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedeemTokens\Actions;

use App\Enums\Role;
use App\Exceptions\Redeem\RedeemConfirmationException;
use App\Filament\Resources\RedeemInvoices\RedeemInvoiceResource;
use App\Filament\Resources\RedeemTokens\Support\VerifyRedeemTokenFormSupport;
use App\Models\RedeemToken;
use App\Services\Redeem\FonnteOtpClient;
use App\Services\Redeem\RedeemConfirmationService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;

class VerifyRedeemTokenAction
{
    public static function make(): Action
    {
        return Action::make('verifyRedeemToken')
            ->label('Verifikasi & Scan Token')
            ->icon('heroicon-o-qr-code')
            ->color('primary')
            ->goldStyle()
            ->modalWidth(Width::FourExtraLarge)
            ->modalHeading('Verifikasi & Konfirmasi Redeem')
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('Konfirmasi Penyerahan Hadiah')
            ->steps([
                Step::make('Token')
                    ->description('Scan QR atau masukkan kode token dari aplikasi member')
                    ->schema([
                        Tabs::make('token_input_method')
                            ->activeTab(1)
                            ->tabs([
                                Tab::make('Scan QR')
                                    ->schema([
                                        View::make('filament.resources.redeem-tokens.partials.token-qr-scanner'),
                                    ]),
                                Tab::make('Ketik Manual')
                                    ->schema([
                                        TextInput::make('token_code')
                                            ->label('Kode Token')
                                            ->required()
                                            ->length(10)
                                            ->maxLength(10)
                                            ->extraInputAttributes([
                                                'style' => 'text-transform: uppercase',
                                                'data-redeem-token-input' => true,
                                            ])
                                            ->dehydrateStateUsing(function (?string $state): string {
                                                return VerifyRedeemTokenFormSupport::normalizeTokenCode((string) $state) ?? '';
                                            }),
                                    ]),
                            ]),
                    ])
                    ->afterValidation(function (Get $get): void {
                        $token = self::resolveAvailableToken($get('token_code'));
                        if ($token === null) {
                            throw new Halt;
                        }

                        if ($token->member === null) {
                            Notification::make()
                                ->title('Token tidak valid')
                                ->body('Data member tidak tersedia untuk token ini.')
                                ->danger()
                                ->send();

                            throw new Halt;
                        }

                        try {
                            app(FonnteOtpClient::class)->send(
                                (string) $token->member->phone_number,
                                (string) $token->token_code,
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
                Step::make('Konfirmasi OTP')
                    ->description('Periksa detail transaksi lalu masukkan OTP dari member')
                    ->schema([
                        View::make('filament.resources.redeem-tokens.partials.token-otp-step')
                            ->viewData(fn (Get $get): array => VerifyRedeemTokenFormSupport::buildOtpStepViewData([
                                'token_code' => $get('token_code'),
                            ])),
                        TextInput::make('otp')
                            ->label('Kode OTP')
                            ->required()
                            ->length(6)
                            ->numeric()
                            ->extraInputAttributes([
                                'data-redeem-otp-input' => true,
                                'autocomplete' => 'one-time-code',
                                'inputmode' => 'numeric',
                            ])
                            ->extraFieldWrapperAttributes([
                                'class' => 'sr-only',
                            ])
                            ->hiddenLabel(),
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
                    $tokenCode = VerifyRedeemTokenFormSupport::normalizeTokenCode((string) ($data['token_code'] ?? ''));
                    if ($tokenCode === null) {
                        Notification::make()
                            ->title('Konfirmasi gagal')
                            ->body('Format kode token tidak valid.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $result = $service->confirm(
                        $tokenCode,
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

    private static function resolveAvailableToken(mixed $rawTokenCode): ?RedeemToken
    {
        $tokenCode = VerifyRedeemTokenFormSupport::normalizeTokenCode((string) $rawTokenCode);
        if ($tokenCode === null) {
            Notification::make()
                ->title('Token tidak valid')
                ->body('Format kode token tidak valid. Harus 10 karakter alfanumerik.')
                ->danger()
                ->send();

            return null;
        }

        $token = VerifyRedeemTokenFormSupport::findAvailableToken($tokenCode);

        if ($token === null) {
            Notification::make()
                ->title('Token tidak valid')
                ->body('Token tidak ditemukan, sudah dipakai, atau kedaluwarsa.')
                ->danger()
                ->send();

            return null;
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

            return null;
        }

        return $token;
    }
}
