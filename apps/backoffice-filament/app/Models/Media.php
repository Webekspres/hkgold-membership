<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'media';

    protected $fillable = [
        'caption',
        'file_name',
        'file_type',
        'file_url',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'profile_photo_id');
    }

    public function contentCoverImages(): HasMany
    {
        return $this->hasMany(ContentCoverImage::class);
    }

    public function rewardImages(): HasMany
    {
        return $this->hasMany(RewardImage::class);
    }

    public function promotionBanners(): HasMany
    {
        return $this->hasMany(PromotionBanner::class);
    }
}
