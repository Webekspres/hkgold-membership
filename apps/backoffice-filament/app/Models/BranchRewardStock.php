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

    protected $table = 'reward_branch_stocks';

    protected $fillable = [
        'branch_id',
        'reward_id',
        'actual_stock',
        'held_stock',
    ];

    protected function casts(): array
    {
        return [
            'actual_stock' => 'integer',
            'held_stock' => 'integer',
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
