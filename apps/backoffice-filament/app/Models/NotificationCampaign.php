<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\NotificationCampaignFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationCampaign extends Model
{
    /** @use HasFactory<NotificationCampaignFactory> */
    use HasFactory, HasUuids;

    protected $table = 'notification_campaigns';

    protected $fillable = [
        'title',
        'body',
        'platforms',
        'criteria_json',
        'targeted_count',
        'accepted_count',
        'failed_count',
        'status',
        'error_message',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'platforms' => 'array',
            'criteria_json' => 'array',
            'targeted_count' => 'integer',
            'accepted_count' => 'integer',
            'failed_count' => 'integer',
            'status' => CampaignStatus::class,
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
