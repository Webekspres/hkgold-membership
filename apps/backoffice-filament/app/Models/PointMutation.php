<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PointMutationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $member_id
 * @property int|null $branch_id
 */
class PointMutation extends Model
{
    /** @use HasFactory<PointMutationFactory> */
    use HasFactory, HasUuids;

    protected $table = 'point_mutations';

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'branch_id',
        'source_id',
        'receipt_number',
        'transaction_type_id',
        'purchase_nominal',
        'points_issued',
        'points_redeemed',
        'balance_snapshot',
        'transaction_date',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_nominal' => 'decimal:2',
            'points_issued' => 'integer',
            'points_redeemed' => 'integer',
            'balance_snapshot' => 'integer',
            'transaction_date' => 'datetime',
            'uploaded_at' => 'datetime',
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

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(PointInjectionBatch::class, 'source_id');
    }
}
