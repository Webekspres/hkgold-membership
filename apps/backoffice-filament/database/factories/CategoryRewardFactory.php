<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryReward>
 */
class CategoryRewardFactory extends Factory
{
    protected $model = CategoryReward::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Perhiasan Emas',
                'Voucher Belanja',
                'Merchandise Eksklusif',
                'Layanan Cuci Emas',
                'Paket Investasi Emas',
                'Hadiah Ulang Tahun',
            ]),
        ];
    }
}
