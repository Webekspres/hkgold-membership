<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FraudSuspectFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FraudSuspect extends Model
{
    /** @use HasFactory<FraudSuspectFactory> */
    use HasFactory, HasUuids;

    protected $table = 'fraud_suspects';

    protected $fillable = [
        'member_1_id',
        'member_2_id',
        'confidence_score',
        'reason',
        'is_resolved',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:2',
            'is_resolved' => 'boolean',
        ];
    }

    public function member1(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_1_id');
    }

    public function member2(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_2_id');
    }
}
