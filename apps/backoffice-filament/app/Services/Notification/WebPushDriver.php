<?php

declare(strict_types=1);

namespace App\Services\Notification;

class WebPushDriver implements PushDriverInterface
{
    public function __construct(private readonly FcmPushDriver $fcmPushDriver) {}

    public function isConfigured(): bool
    {
        return filled(config('notifications.webpush.vapid_public_key'))
            && $this->fcmPushDriver->isConfigured();
    }

    /**
     * @param  array<string, string>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): PushDeliveryResult
    {
        if (! $this->isConfigured()) {
            return PushDeliveryResult::failed('Web Push belum dikonfigurasi (VAPID public key + FCM credential).');
        }

        return $this->fcmPushDriver->sendToToken($token, $title, $body, $data);
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $data
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): PushDeliveryResult
    {
        if (! $this->isConfigured()) {
            return PushDeliveryResult::failed('Web Push belum dikonfigurasi (VAPID public key + FCM credential).');
        }

        return $this->fcmPushDriver->sendMulticast($tokens, $title, $body, $data);
    }
}
