<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentType;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory, HasUuids;

    protected $table = 'contents';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'body_content',
        'event_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'event_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
