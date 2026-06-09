<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BatchStatus;
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

    protected $fillable = [
        'branch_id',
        'uploaded_by_id',
        'filename',
        'file_url',
        'status',
        'total_rows',
        'processed_rows',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'status' => BatchStatus::class,
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'uploaded_by_id');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(PointMutation::class, 'batch_id');
    }
}
