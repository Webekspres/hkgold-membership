<?php

declare(strict_types=1);

namespace App\Exceptions\ChangePhone;

use RuntimeException;

class ChangePhoneApprovalException extends RuntimeException
{
    public static function failed(string $message): self
    {
        return new self($message);
    }
}
