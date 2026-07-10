<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MemberAnomalyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAnomaly extends Model
{
    /** @use HasFactory<MemberAnomalyFactory> */
    use HasFactory, HasUuids;

    protected $table = 'member_anomalies';

    public const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'last_active_at',
        'inactivity_duration_days',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
            'inactivity_duration_days' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
