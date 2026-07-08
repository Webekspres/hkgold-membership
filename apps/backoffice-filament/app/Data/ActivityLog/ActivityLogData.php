<?php

declare(strict_types=1);

namespace App\Data\ActivityLog;

use App\Enums\ActivityLogAction;

readonly class ActivityLogData
{
    /**
     * @param  array<string, mixed>|null  $beforeJson
     * @param  array<string, mixed>|null  $afterJson
     */
    public function __construct(
        public ActivityLogAction $action,
        public string $description,
        public string $auditableType,
        public string $auditableId,
        public ?string $userId,
        public ?array $beforeJson,
        public ?array $afterJson,
        public string $ipAddress,
    ) {}
}
