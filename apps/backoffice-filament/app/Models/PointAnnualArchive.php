<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PointAnnualArchiveFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointAnnualArchive extends Model
{
    /** @use HasFactory<PointAnnualArchiveFactory> */
    use HasFactory, HasUuids;

    protected $table = 'point_annual_archives';

    public const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'points_snapshot',
        'archive_year',
    ];

    protected function casts(): array
    {
        return [
            'points_snapshot' => 'decimal:2',
            'archive_year' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
