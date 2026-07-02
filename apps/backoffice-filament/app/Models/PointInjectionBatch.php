<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PointInjectionBatchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointInjectionBatch extends Model
{
    /** @use HasFactory<PointInjectionBatchFactory> */
    use HasFactory, HasUuids;

    protected $table = 'point_injection_batches';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'media_id',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'total_points_injected',
        'resolved',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'total_rows'            => 'integer',
            'successful_rows'       => 'integer',
            'failed_rows'           => 'integer',
            'total_points_injected' => 'integer',
            'resolved'              => 'boolean',
            'uploaded_at'           => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PointInjectionDetail::class, 'batch_id');
    }

    public function pointMutations(): HasMany
    {
        return $this->hasMany(PointMutation::class, 'source_id');
    }
}
