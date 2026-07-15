<?php

declare(strict_types=1);

namespace App\Exceptions\Redeem;

use RuntimeException;

class RedeemConfirmationException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'details' => $this->details,
            ],
        ];
    }

    public static function tokenNotFound(): self
    {
        return new self('TOKEN_NOT_FOUND', 'Token redeem tidak ditemukan.');
    }

    public static function tokenAlreadyUsed(): self
    {
        return new self('TOKEN_ALREADY_USED', 'Token redeem sudah digunakan.');
    }

    public static function tokenExpired(): self
    {
        return new self('TOKEN_EXPIRED', 'Token redeem sudah kedaluwarsa.');
    }

    public static function branchMismatch(): self
    {
        return new self('BRANCH_MISMATCH', 'Token redeem tidak untuk cabang Anda.');
    }

    public static function stockInconsistent(): self
    {
        return new self('STOCK_INCONSISTENT', 'Stok reward di cabang tidak konsisten atau habis.');
    }

    public static function staffRequired(): self
    {
        return new self('STAFF_REQUIRED', 'Akun Anda belum terhubung ke data staf cabang.');
    }

    public static function otpSendFailed(string $reason): self
    {
        return new self('OTP_SEND_FAILED', $reason);
    }

    public static function otpInvalid(string $reason): self
    {
        return new self('OTP_INVALID', $reason);
    }
}
