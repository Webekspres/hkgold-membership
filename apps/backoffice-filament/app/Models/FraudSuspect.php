<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FraudSuspectFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraudSuspect extends Model
{
    /** @use HasFactory<FraudSuspectFactory> */
    use HasFactory, HasUuids;

    protected $table = 'fraud_suspects';

    protected $fillable = [
        'detected_name',
        'detected_birth_date',
        'suspect_member_ids',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'detected_birth_date' => 'datetime',
            'suspect_member_ids' => 'array',
        ];
    }
}
