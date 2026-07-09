<?php

declare(strict_types=1);

namespace App\Services\Notification;

readonly class PushDeliveryResult
{
    public function __construct(
        public bool $success,
        public ?string $errorMessage = null,
        public int $successCount = 0,
        public int $failureCount = 0,
    ) {}

    public static function success(int $count = 1): self
    {
        return new self(
            success: true,
            successCount: $count,
            failureCount: 0,
        );
    }

    public static function failed(string $message, int $failureCount = 1): self
    {
        return new self(
            success: false,
            errorMessage: $message,
            successCount: 0,
            failureCount: $failureCount,
        );
    }

    public static function multicast(int $successCount, int $failureCount, ?string $errorMessage = null): self
    {
        return new self(
            success: $successCount > 0,
            errorMessage: $errorMessage,
            successCount: $successCount,
            failureCount: $failureCount,
        );
    }
}
