<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RegencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
{
    /** @use HasFactory<RegencyFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'cities';

    protected $fillable = [
        'province_id',
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

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'city_id');
    }
}
