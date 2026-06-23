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
        return [
            'province_id' => Province::factory(),
            'nama' => 'Kota '.fake()->unique()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
