<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RedeemTokenFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemToken extends Model
{
    /** @use HasFactory<RedeemTokenFactory> */
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'redeem_tokens';

    protected $fillable = [
        'member_id',
        'reward_id',
        'branch_id',
        'token_code',
        'held_points',
        'is_used',
        'expired_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'held_points' => 'integer',
            'is_used' => 'boolean',
            'expired_at' => 'datetime',
            'released_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<RedeemToken>  $query
     * @return Builder<RedeemToken>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_used', false)
            ->where('expired_at', '>', now());
    }

    /**
     * @param  Builder<RedeemToken>  $query
     * @return Builder<RedeemToken>
     */
    public function scopeExpiredUnused(Builder $query): Builder
    {
        return $query
            ->where('is_used', false)
            ->where('expired_at', '<=', now())
            ->whereNull('released_at');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
