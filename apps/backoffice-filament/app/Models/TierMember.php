<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TierStatus;
use App\Models\Concerns\HasAuditableActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TierMember extends Model
{
    use HasAuditableActivityLogs;

    protected $table = 'tier_members';

    protected $fillable = [
        'tier_code',
        'min_points',
        'max_points',
    ];

    protected function casts(): array
    {
        return [
            'tier_code' => TierStatus::class,
            'min_points' => 'integer',
            'max_points' => 'integer',
        ];
    }

    public function conversionRules(): HasMany
    {
        return $this->hasMany(ConversionRule::class, 'tier_member_id');
    }
}
