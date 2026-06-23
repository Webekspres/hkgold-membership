<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Village>
 */
class VillageFactory extends Factory
{
    protected $model = Village::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefix = fake()->randomElement(['Kelurahan', 'Desa']);

        return [
            'sub_district_id' => District::factory(),
            'nama' => $prefix.' '.fake()->lastName(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
