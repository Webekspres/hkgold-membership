<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InjectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointInjectionDetail extends Model
{
    protected $table = 'point_injection_details';

    public $timestamps = false;

    protected $fillable = [
        'batch_id',
        'row_number',
        'raw_member_number',
        'raw_branch_code',
        'purchase_nominal',
        'transaction_type_id',
        'transaction_date',
        'calculated_points',
        'status',
        'error_message',
        'processed_at',
        'receipt_number',
    ];

    protected function casts(): array
    {
        return [
            'purchase_nominal' => 'decimal:2',
            'calculated_points' => 'integer',
            'row_number' => 'integer',
            'transaction_date' => 'datetime',
            'processed_at' => 'datetime',
            'status' => InjectionStatus::class,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PointInjectionBatch::class, 'batch_id');
    }

    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    /**
     * Resolve the member by matching raw_member_number to members.member_number.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'raw_member_number', 'member_number');
    }
}
