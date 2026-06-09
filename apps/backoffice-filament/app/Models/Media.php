<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, HasUuids;

    protected $table = 'media';

    protected $fillable = [
        'reward_id',
        'caption',
        'file_name',
        'file_type',
        'file_url',
        'file_size',
    ];

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'profile_photo_id');
    }

    public function content(): HasOne
    {
        return $this->hasOne(Content::class);
    }
}
