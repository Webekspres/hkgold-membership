<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasAuditableActivityLogs;
use Database\Factories\RewardFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    /** @use HasFactory<RewardFactory> */
    use HasAuditableActivityLogs, HasFactory, HasUuids;

    protected $table = 'rewards';

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'points_required',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function categoryReward(): BelongsTo
    {
        return $this->belongsTo(CategoryReward::class, 'category_id');
    }

    public function rewardImages(): HasMany
    {
        return $this->hasMany(RewardImage::class)->orderBy('sort_order');
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(BranchRewardStock::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(RedeemInvoice::class);
    }
}
