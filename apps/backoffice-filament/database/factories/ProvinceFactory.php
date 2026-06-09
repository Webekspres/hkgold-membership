<?php

declare(strict_types=1);

namespace Database\Factories;

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
        $provinces = [
            'Jawa Tengah',
            'Jawa Timur',
            'DKI Jakarta',
            'Jawa Barat',
            'DI Yogyakarta',
            'Kalimantan Barat',
            'Sumatera Utara',
            'Bali',
            'Sulawesi Selatan',
            'Banten',
        ];

        return [
            'name' => fake()->randomElement($provinces),
        ];
    }
}
