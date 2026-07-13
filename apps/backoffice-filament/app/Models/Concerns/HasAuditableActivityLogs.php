<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasAuditableActivityLogs
{
    public function auditableActivityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'auditable_id', $this->getKeyName())
            ->where('auditable_type', class_basename($this));
    }
}
