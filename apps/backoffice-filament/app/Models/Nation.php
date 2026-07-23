<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nation extends Model
{
    /** @use HasFactory<NationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'nations';

    protected $fillable = [
        'nation_code',
        'iso2',
        'iso3',
        'nama',
        'mata_uang',
        'kode_mata_uang',
        'simbol_mata_uang',
        'satuan_berat',
        'satuan_panjang',
        'latitude',
        'longitude',
        'is_provinsi',
        'is_kabkota',
        'is_kecamatan',
        'is_kelurahan',
        'is_kodepos',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:11',
            'longitude' => 'decimal:11',
            'is_provinsi' => 'boolean',
            'is_kabkota' => 'boolean',
            'is_kecamatan' => 'boolean',
            'is_kelurahan' => 'boolean',
            'is_kodepos' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }
}
