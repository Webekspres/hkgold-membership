<?php

declare(strict_types=1);

namespace App\Services\Redeem;

use App\Exceptions\Redeem\RedeemConfirmationException;
use Illuminate\Support\Facades\Http;

class FonnteOtpClient
{
    public function send(string $phone, string $redeemTokenCode): void
    {
        $response = Http::withHeaders([
            'X-Internal-Secret' => (string) config('redeem.mobile_api.internal_secret'),
        ])->post($this->endpoint('/internal/otp/send'), [
            'phone' => $phone,
            'redeemTokenCode' => $redeemTokenCode,
        ]);

        if (! $response->successful() || $response->json('success') !== true) {
            throw RedeemConfirmationException::otpSendFailed(
                (string) ($response->json('message') ?? 'Gagal mengirim OTP'),
            );
        }
    }

    public function verify(string $phone, string $redeemTokenCode, string $otp): void
    {
        $response = Http::withHeaders([
            'X-Internal-Secret' => (string) config('redeem.mobile_api.internal_secret'),
        ])->post($this->endpoint('/internal/otp/verify'), [
            'phone' => $phone,
            'redeemTokenCode' => $redeemTokenCode,
            'otp' => $otp,
        ]);

        if (! $response->successful() || $response->json('success') !== true) {
            throw RedeemConfirmationException::otpInvalid(
                (string) ($response->json('message') ?? 'OTP tidak valid'),
            );
        }
    }

    private function endpoint(string $path): string
    {
        $base = rtrim((string) config('redeem.mobile_api.url'), '/');

        return $base.$path;
    }
}
