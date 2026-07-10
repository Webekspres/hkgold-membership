<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BranchRewardStockFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchRewardStock extends Model
{
    /** @use HasFactory<BranchRewardStockFactory> */
    use HasFactory, HasUuids;

    protected $table = 'branch_reward_stocks';

    protected $fillable = [
        'branch_id',
        'reward_id',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}
