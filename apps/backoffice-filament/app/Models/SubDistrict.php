<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SubDistrictFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDistrict extends Model
{
    /** @use HasFactory<SubDistrictFactory> */
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class, 'sub_district_id');
    }

    public function postalCodes(): HasMany
    {
        return $this->hasMany(PostalCode::class, 'sub_district_id');
    }
}
