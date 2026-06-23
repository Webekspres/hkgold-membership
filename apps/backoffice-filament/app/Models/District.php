<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DistrictFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    /** @use HasFactory<DistrictFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'sub_districts';

    protected $fillable = [
        'city_id',
        'nama',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:11',
            'longitude' => 'decimal:11',
        ];
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'city_id');
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class, 'sub_district_id');
    }

    public function getRegencyIdAttribute(): ?int
    {
        return isset($this->attributes['city_id']) ? (int) $this->attributes['city_id'] : null;
    }

    public function setRegencyIdAttribute(int|string $value): void
    {
        $this->attributes['city_id'] = $value;
    }
}
