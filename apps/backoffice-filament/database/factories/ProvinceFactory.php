<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Nation;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Province>
 */
class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nation_id' => Nation::query()->value('id') ?? Nation::factory(),
            'nama' => fake()->unique()->state(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
