<?php

declare(strict_types=1);

namespace App\Data\Loyalty;

readonly class ProcessBatchSummary
{
    public function __construct(
        public int $uniqueMembers,
        public int $totalRows,
        public int $totalPoints,
        public string $totalNominal,
    ) {}
}
