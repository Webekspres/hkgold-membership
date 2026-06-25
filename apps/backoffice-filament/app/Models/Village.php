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

    public function subDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
