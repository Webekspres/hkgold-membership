<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Province;
use App\Models\Regency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Regency>
 */
class RegencyFactory extends Factory
{
    protected $model = Regency::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regencies = [
            'Kota Semarang',
            'Kota Solo',
            'Kota Pontianak',
            'Kota Yogyakarta',
            'Kota Surabaya',
            'Kota Bandung',
            'Kota Jakarta Selatan',
            'Kabupaten Demak',
            'Kabupaten Sleman',
            'Kota Malang',
        ];

        return [
            'province_id' => Province::factory(),
            'name' => fake()->randomElement($regencies),
        ];
    }
}
