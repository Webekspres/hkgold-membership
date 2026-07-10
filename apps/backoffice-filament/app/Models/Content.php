<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentType;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory, HasUuids;

    protected $table = 'contents';

    protected $fillable = [
        'type',
        'title',
        'body',
        'location',
        'start_date',
        'end_date',
        'is_published',
        'media_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
