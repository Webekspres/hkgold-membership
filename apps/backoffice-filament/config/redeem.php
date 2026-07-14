<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Mobile API (ElysiaJS)
    |--------------------------------------------------------------------------
    |
    | Backoffice memanggil api-elysia untuk OTP WhatsApp (Fonnte) dan operasi
    | redeem yang dieksekusi di sisi API. Secret dipakai header service-to-service.
    |
    */

    'mobile_api' => [
        'url' => env('MOBILE_API_URL'),
        'internal_secret' => env('MOBILE_API_INTERNAL_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redeem timing (mirror api-elysia — ubah via Doppler)
    |--------------------------------------------------------------------------
    */

    'token_expiry_minutes' => (int) env('REDEEM_TOKEN_EXPIRY_MINUTES', 4320),

    'otp_expiry_minutes' => (int) env('REDEEM_OTP_EXPIRY_MINUTES', 5),

];
