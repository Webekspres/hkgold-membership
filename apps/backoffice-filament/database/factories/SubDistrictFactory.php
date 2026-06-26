<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\SubDistrict;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubDistrict>
 */
class SubDistrictFactory extends Factory
{
    protected $model = SubDistrict::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'nama' => fake()->unique()->streetName(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
