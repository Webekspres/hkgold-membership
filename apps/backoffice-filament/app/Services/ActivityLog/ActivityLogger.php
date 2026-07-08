<?php

declare(strict_types=1);

namespace App\Services\ActivityLog;

use App\Data\ActivityLog\ActivityLogData;
use App\Enums\ActivityLogAction;
use App\Jobs\PersistActivityLogJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function log(
        ActivityLogAction $action,
        string $description,
        Model $auditable,
        string $ipAddress,
        ?array $before = null,
        ?array $after = null,
        ?User $actor = null,
    ): void {
        $this->dispatch(
            action: $action,
            description: $description,
            auditableType: class_basename($auditable),
            auditableId: (string) $auditable->getKey(),
            ipAddress: $ipAddress,
            before: $before,
            after: $after,
            actor: $actor,
        );
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function logWithKey(
        ActivityLogAction $action,
        string $description,
        string $auditableType,
        string $auditableId,
        string $ipAddress,
        ?array $before = null,
        ?array $after = null,
        ?User $actor = null,
    ): void {
        $this->dispatch(
            action: $action,
            description: $description,
            auditableType: $auditableType,
            auditableId: $auditableId,
            ipAddress: $ipAddress,
            before: $before,
            after: $after,
            actor: $actor,
        );
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function dispatch(
        ActivityLogAction $action,
        string $description,
        string $auditableType,
        string $auditableId,
        string $ipAddress,
        ?array $before = null,
        ?array $after = null,
        ?User $actor = null,
    ): void {
        $data = new ActivityLogData(
            action: $action,
            description: $description,
            auditableType: $auditableType,
            auditableId: $auditableId,
            userId: $actor?->id,
            beforeJson: $before,
            afterJson: $after,
            ipAddress: $ipAddress,
        );

        try {
            PersistActivityLogJob::dispatch($data)->afterCommit();
        } catch (Throwable $exception) {
            Log::warning('Gagal mengantre activity log.', [
                'action' => $action->value,
                'auditable_type' => $data->auditableType,
                'auditable_id' => $data->auditableId,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
