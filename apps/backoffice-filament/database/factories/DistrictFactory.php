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
        return [
            'city_id' => Regency::factory(),
            'nama' => fake()->unique()->streetName(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
