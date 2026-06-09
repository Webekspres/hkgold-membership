<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\RedeemInvoiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedeemInvoice extends Model
{
    /** @use HasFactory<RedeemInvoiceFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'redeem_invoices';

    protected $fillable = [
        'invoice_number',
        'member_id',
        'branch_id',
        'reward_id',
        'points_deducted',
        'status',
        'qr_token',
        'expires_at',
        'qr_expires_at',
        'confirmed_by_id',
        'cancelled_by_id',
    ];

    protected function casts(): array
    {
        return [
            'points_deducted' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'expires_at' => 'datetime',
            'qr_expires_at' => 'datetime',
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

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'confirmed_by_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'cancelled_by_id');
    }
}
