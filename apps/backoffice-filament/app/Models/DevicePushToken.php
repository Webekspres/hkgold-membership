<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevicePushTokenPlatform;
use Database\Factories\DevicePushTokenFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevicePushToken extends Model
{
    /** @use HasFactory<DevicePushTokenFactory> */
    use HasFactory, HasUuids;

    protected $table = 'device_push_tokens';

    protected $fillable = [
        'user_id',
        'device_uuid',
        'platform',
        'token',
        'last_used_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => DevicePushTokenPlatform::class,
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<DevicePushToken>  $query
     * @return Builder<DevicePushToken>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
