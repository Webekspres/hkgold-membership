<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerns;

use App\Filament\Resources\ActivityLogs\RelationManagers\ActivityLogsRelationManager;

trait RegistersActivityLogsRelationManager
{
    /**
     * @return class-string<ActivityLogsRelationManager>
     */
    protected static function activityLogsRelationManager(): string
    {
        return ActivityLogsRelationManager::class;
    }
}
