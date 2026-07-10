<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryReward;
use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reward>
 */
class RewardFactory extends Factory
{
    protected $model = Reward::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rewards = [
            ['name' => 'Cincin Emas 22K 2 gram', 'points' => 85000],
            ['name' => 'Kalung Antam 1 gram', 'points' => 120000],
            ['name' => 'Voucher Belanja Rp 500.000', 'points' => 45000],
            ['name' => 'Gelang Emas 1 gram', 'points' => 65000],
            ['name' => 'Layanan Cuci Emas Premium', 'points' => 15000],
            ['name' => 'Liontin Bulan Sabit 0.5 gram', 'points' => 38000],
            ['name' => 'Tas Merchandise HK Gold VIP', 'points' => 25000],
            ['name' => 'Paket Tabungan Emas 0.25 gram', 'points' => 32000],
        ];

        $reward = fake()->randomElement($rewards);

        return [
            'category_reward_id' => CategoryReward::factory(),
            'name' => $reward['name'],
            'description' => 'Hadiah loyalitas HK GOLD VIP untuk member setia. '.fake('id_ID')->sentence(),
            'points_required' => $reward['points'],
            'valid_until' => fake()->optional(0.7)->dateTimeBetween('+1 month', '+1 year')?->format('Y-m-d'),
            'is_active' => true,
        ];
    }
}
