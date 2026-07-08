<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationPlatform;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory, HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'notification_key',
        'title',
        'body',
        'platform',
        'status',
        'data_payload',
        'read_at',
        'sent_at',
        'failed_at',
        'error_message',
        'attempt_count',
    ];

    protected function casts(): array
    {
        return [
            'platform' => NotificationPlatform::class,
            'status' => NotificationDeliveryStatus::class,
            'data_payload' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'attempt_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
