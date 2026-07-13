<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Concerns\HasAuditableActivityLogs;
use App\Observers\ContentObserver;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ContentObserver::class)]
class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasAuditableActivityLogs, HasFactory, HasUuids;

    protected $table = 'contents';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'body_content',
        'event_date',
        'status',
        'is_staged',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'event_date' => 'datetime',
            'status' => ContentStatus::class,
            'is_staged' => 'boolean',
        ];
    }

    public function contentCoverImages(): HasMany
    {
        return $this->hasMany(ContentCoverImage::class)->orderBy('sort_order');
    }
}
