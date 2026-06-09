<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TierStatus;
use Database\Factories\LoyaltyConfigFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyConfig extends Model
{
    /** @use HasFactory<LoyaltyConfigFactory> */
    use HasFactory, HasUuids;

    protected $table = 'loyalty_configs';

    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    protected $fillable = [
        'tier',
        'multiplier_cost',
    ];

    protected function casts(): array
    {
        return [
            'tier' => TierStatus::class,
            'multiplier_cost' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }
}
