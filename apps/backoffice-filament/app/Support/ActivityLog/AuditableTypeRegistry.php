<?php

declare(strict_types=1);

namespace App\Support\ActivityLog;

use Illuminate\Database\Eloquent\Model;

class AuditableTypeRegistry
{
    public static function forModel(Model $model): string
    {
        return class_basename($model);
    }
}
