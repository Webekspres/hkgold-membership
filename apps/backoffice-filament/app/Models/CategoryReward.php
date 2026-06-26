<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryRewardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryReward extends Model
{
    /** @use HasFactory<CategoryRewardFactory> */
    use HasFactory;

    protected $table = 'category_rewards';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class, 'category_id');
    }
}
