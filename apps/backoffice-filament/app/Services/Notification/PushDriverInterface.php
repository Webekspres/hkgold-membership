<?php

declare(strict_types=1);

namespace App\Services\Notification;

interface PushDriverInterface
{
    /**
     * @param  array<string, string>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): PushDeliveryResult;

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, string>  $data
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = []): PushDeliveryResult;
}
