<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RedeemInvoiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemInvoice extends Model
{
    /** @use HasFactory<RedeemInvoiceFactory> */
    use HasFactory, HasUuids;

    protected $table = 'redeem_invoices';

    protected $fillable = [
        'invoice_number',
        'member_id',
        'staff_id',
        'branch_id',
        'reward_id',
        'points_redeemed',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'points_redeemed' => 'integer',
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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
