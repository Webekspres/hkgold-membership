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

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'last_mutation_at',
        'hoarded_points',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'last_mutation_at' => 'datetime',
            'hoarded_points' => 'integer',
            'detected_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
