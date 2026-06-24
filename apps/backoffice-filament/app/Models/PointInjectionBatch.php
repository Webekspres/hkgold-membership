<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PointInjectionBatchFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointInjectionBatch extends Model
{
    /** @use HasFactory<PointInjectionBatchFactory> */
    use HasFactory, HasUuids;

    protected $table = 'point_injection_batches';

    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'file_name',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'total_points_injected',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'total_points_injected' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
