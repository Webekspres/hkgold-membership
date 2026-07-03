<?php

declare(strict_types=1);

namespace App\Data\Loyalty;

readonly class ProcessBatchResult
{
    public function __construct(
        public string $batchId,
        public int $rowsProcessed,
        public int $totalPointsInjected,
        public int $uniqueMembers,
    ) {}
}
