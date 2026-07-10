<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    protected $model = District::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $districts = [
            'Semarang Tengah',
            'Laweyan',
            'Pontianak Kota',
            'Gondokusuman',
            'Genteng',
            'Coblong',
            'Kebayoran Baru',
            'Demak',
            'Depok',
            'Klojen',
        ];

        return [
            'regency_id' => Regency::factory(),
            'name' => fake()->randomElement($districts),
        ];
    }
}
