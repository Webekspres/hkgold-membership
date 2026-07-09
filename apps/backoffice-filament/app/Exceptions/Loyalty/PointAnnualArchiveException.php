<?php

declare(strict_types=1);

namespace App\Exceptions\Loyalty;

use RuntimeException;

class PointAnnualArchiveException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public static function archiveAlreadyExists(int $archiveYear): self
    {
        return new self(
            'ARCHIVE_ALREADY_EXISTS',
            'Arsip poin tahun '.$archiveYear.' sudah pernah dijalankan.',
            ['archive_year' => $archiveYear],
        );
    }

    public static function onlyPreviousYearAllowed(int $expectedYear): self
    {
        return new self(
            'ONLY_PREVIOUS_YEAR_ALLOWED',
            'Arsip poin hanya boleh dijalankan untuk tahun '.$expectedYear.'.',
            ['expected_year' => $expectedYear],
        );
    }

    public static function actorMustBeAdministrator(): self
    {
        return new self(
            'ACTOR_MUST_BE_ADMINISTRATOR',
            'Hanya administrator yang dapat menjalankan arsip poin.',
        );
    }
}
