<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointAnnualArchivePeriod extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'point_annual_archive_periods';

    protected $fillable = [
        'archive_year',
        'name',
        'total_members',
        'frozen_points_total',
        'redeemed_points_total',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archive_year' => 'integer',
            'total_members' => 'integer',
            'frozen_points_total' => 'integer',
            'redeemed_points_total' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    public function archives(): HasMany
    {
        return $this->hasMany(PointAnnualArchive::class, 'period_id');
    }

    public function getPreviousPeriod(): ?self
    {
        return self::query()
            ->where('archive_year', $this->archive_year - 1)
            ->first();
    }

    public function getMembersGrowthPercent(): ?float
    {
        $prev = $this->getPreviousPeriod();
        if ($prev === null || $prev->total_members === 0) {
            return null;
        }

        return (($this->total_members - $prev->total_members) / $prev->total_members) * 100;
    }

    public function getFrozenPointsGrowthPercent(): ?float
    {
        $prev = $this->getPreviousPeriod();
        if ($prev === null || $prev->frozen_points_total === 0) {
            return null;
        }

        return (($this->frozen_points_total - $prev->frozen_points_total) / $prev->frozen_points_total) * 100;
    }

    public function getRedeemedPointsGrowthPercent(): ?float
    {
        $prev = $this->getPreviousPeriod();
        if ($prev === null || $prev->redeemed_points_total === 0) {
            return null;
        }

        return (($this->redeemed_points_total - $prev->redeemed_points_total) / $prev->redeemed_points_total) * 100;
    }
}
