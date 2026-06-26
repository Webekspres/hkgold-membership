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
        ];

        $reward = fake()->randomElement($rewards);

        return [
            'category_id' => CategoryReward::factory(),
            'name' => $reward['name'],
            'sku' => 'RW-'.fake()->unique()->regexify('[A-Z0-9]{8}'),
            'description' => 'Hadiah loyalitas HK GOLD VIP untuk member setia. '.fake('id_ID')->sentence(),
            'points_required' => $reward['points'],
            'is_active' => true,
            'start_at' => now()->subMonth(),
            'end_at' => now()->addYear(),
        ];
    }
}
