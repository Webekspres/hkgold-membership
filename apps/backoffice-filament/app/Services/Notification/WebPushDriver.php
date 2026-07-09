<?php

declare(strict_types=1);

namespace App\Services\Notification;

class WebPushDriver implements PushDriverInterface
{
    public function isConfigured(): bool
    {
        return filled(config('notifications.webpush.vapid_public_key'))
            && filled(config('notifications.webpush.vapid_private_key'));
    }

    /**
     * @param  array<string, string>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): PushDeliveryResult
    {
        if (! $this->isConfigured()) {
            return PushDeliveryResult::failed('Web Push VAPID tidak dikonfigurasi.');
        }

        return PushDeliveryResult::failed('Web Push driver belum diimplementasi.');
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $data
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): PushDeliveryResult
    {
        if (! $this->isConfigured()) {
            return PushDeliveryResult::failed('Web Push VAPID tidak dikonfigurasi.');
        }

        return PushDeliveryResult::failed('Web Push driver belum diimplementasi.', count($tokens));
    }
}
