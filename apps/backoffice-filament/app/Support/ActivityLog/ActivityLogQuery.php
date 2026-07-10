<?php

declare(strict_types=1);

namespace App\Support\ActivityLog;

use App\Enums\ActivityLogAction;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogQuery
{
    public static function global(): Builder
    {
        return ActivityLog::query()
            ->with('user')
            ->latest('created_at');
    }

    public static function forSubject(string $type, string $id): Builder
    {
        return ActivityLog::query()
            ->with('user')
            ->where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->latest('created_at');
    }

    public static function actorLabel(ActivityLog $log): string
    {
        return $log->user?->full_name ?? 'Sistem';
    }

    public static function displayAction(ActivityLog $log): string
    {
        $action = ActivityLogAction::tryFrom($log->action);

        return $action?->label() ?? $log->action;
    }

    /**
     * @return array<string, string>
     */
    public static function actionFilterOptions(): array
    {
        $options = [];

        foreach (ActivityLogAction::cases() as $action) {
            $options[$action->value] = $action->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function auditableTypeFilterOptions(): array
    {
        return ActivityLog::query()
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type', 'auditable_type')
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    public static function formatJsonState(?array $state): string
    {
        if ($state === null || $state === []) {
            return '—';
        }

        return collect($state)
            ->map(function (mixed $value, string|int $key): string {
                if (is_array($value)) {
                    return "{$key}: ".json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                return "{$key}: {$value}";
            })
            ->implode("\n");
    }
}
