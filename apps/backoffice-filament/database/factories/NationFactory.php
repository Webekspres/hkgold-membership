<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Nation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nation>
 */
class NationFactory extends Factory
{
    protected $model = Nation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nation_code' => 62,
            'iso2' => 'ID',
            'iso3' => 'IDR',
            'nama' => 'Indonesia',
            'mata_uang' => 'Rupiah',
            'kode_mata_uang' => 'Rp',
            'simbol_mata_uang' => 'Rp',
            'satuan_berat' => 'Kg',
            'satuan_panjang' => 'cm',
            'latitude' => -7.1,
            'longitude' => 1.1,
            'is_provinsi' => true,
            'is_kabkota' => true,
            'is_kecamatan' => true,
            'is_kelurahan' => true,
            'is_kodepos' => true,
            'is_active' => true,
        ];
    }
}
