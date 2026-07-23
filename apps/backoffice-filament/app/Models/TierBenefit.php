<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TierBenefitFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierBenefit extends Model
{
    /** @use HasFactory<TierBenefitFactory> */
    use HasFactory, HasUuids;

    protected $table = 'tier_benefits';

    protected $fillable = [
        'tier_member_id',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tier_member_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tierMember(): BelongsTo
    {
        return $this->belongsTo(TierMember::class, 'tier_member_id');
    }
}
