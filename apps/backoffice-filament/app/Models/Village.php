<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VillageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    /** @use HasFactory<VillageFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'villages';

    protected $fillable = [
        'sub_district_id',
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

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'sub_district_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function getDistrictIdAttribute(): ?int
    {
        return isset($this->attributes['sub_district_id']) ? (int) $this->attributes['sub_district_id'] : null;
    }

    public function setDistrictIdAttribute(int|string $value): void
    {
        $this->attributes['sub_district_id'] = $value;
    }
}
