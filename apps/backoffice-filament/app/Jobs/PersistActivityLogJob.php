<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Data\ActivityLog\ActivityLogData;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PersistActivityLogJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public function __construct(public readonly ActivityLogData $data)
    {
        $this->onQueue('activity-log');
    }

    public function handle(): void
    {
        ActivityLog::query()->create([
            'user_id' => $this->data->userId,
            'action' => $this->data->action->value,
            'description' => $this->data->description,
            'auditable_type' => $this->data->auditableType,
            'auditable_id' => $this->data->auditableId,
            'before_json' => $this->data->beforeJson,
            'after_json' => $this->data->afterJson,
            'ip_address' => $this->data->ipAddress,
            'created_at' => now(),
        ]);
    }
}
