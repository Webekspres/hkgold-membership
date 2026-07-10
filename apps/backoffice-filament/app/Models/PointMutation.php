<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MutationType;
use Database\Factories\PointMutationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointMutation extends Model
{
    /** @use HasFactory<PointMutationFactory> */
    use HasFactory, HasUuids;

    protected $table = 'point_mutations';

    public const CREATED_AT = null;

    protected $fillable = [
        'member_id',
        'branch_id',
        'batch_id',
        'type',
        'points',
        'transaction_date',
        'description',
        'transaction_amount',
        'invoice_reference',
        'upload_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => MutationType::class,
            'points' => 'decimal:2',
            'transaction_amount' => 'decimal:2',
            'transaction_date' => 'datetime',
            'upload_date' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PointInjectionBatch::class, 'batch_id');
    }
}
