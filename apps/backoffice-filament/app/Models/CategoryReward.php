<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryRewardFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryReward extends Model
{
    /** @use HasFactory<CategoryRewardFactory> */
    use HasFactory, HasUuids;

    protected $table = 'category_rewards';

    protected $fillable = [
        'name',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}
