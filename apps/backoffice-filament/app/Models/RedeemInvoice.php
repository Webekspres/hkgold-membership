<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RedeemStatus;
use App\Models\Concerns\HasAuditableActivityLogs;
use Database\Factories\RedeemInvoiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemInvoice extends Model
{
    /** @use HasFactory<RedeemInvoiceFactory> */
    use HasAuditableActivityLogs, HasFactory, HasUuids;

    protected $table = 'redeem_invoices';

    protected $fillable = [
        'invoice_number',
        'member_id',
        'staff_id',
        'branch_id',
        'reward_id',
        'redeem_token_id',
        'points_redeemed',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'points_redeemed' => 'integer',
            'status' => RedeemStatus::class,
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

    public function redeemToken(): BelongsTo
    {
        return $this->belongsTo(RedeemToken::class, 'redeem_token_id');
    }
}
