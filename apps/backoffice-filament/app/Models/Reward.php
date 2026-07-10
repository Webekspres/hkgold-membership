<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RewardFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    /** @use HasFactory<RewardFactory> */
    use HasFactory, HasUuids;

    protected $table = 'rewards';

    protected $fillable = [
        'category_reward_id',
        'name',
        'description',
        'points_required',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'decimal:2',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function categoryReward(): BelongsTo
    {
        return $this->belongsTo(CategoryReward::class);
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchRewardStock::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
