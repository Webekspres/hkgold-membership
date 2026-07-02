<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TierStatus;
use Database\Factories\PointAnnualArchiveFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointAnnualArchive extends Model
{
    /** @use HasFactory<PointAnnualArchiveFactory> */
    use HasFactory, HasUuids;

    protected $table = 'point_annual_archives';

    public $timestamps = false;

    protected $fillable = [
        'period_id',
        'member_id',
        'frozen_points_total',
        'highest_point',
        'last_tier_position',
        'frozen_at',
    ];

    protected function casts(): array
    {
        return [
            'frozen_points_total' => 'integer',
            'highest_point' => 'integer',
            'last_tier_position' => TierStatus::class,
            'frozen_at' => 'datetime',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PointAnnualArchivePeriod::class, 'period_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
