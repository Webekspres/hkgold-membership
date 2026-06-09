<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FraudSuspect;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FraudSuspect>
 */
class FraudSuspectFactory extends Factory
{
    protected $model = FraudSuspect::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reasons = [
            'Transaksi poin identik dalam rentang 5 menit di cabang berbeda',
            'Nomor HP dan alamat email mirip dengan member lain',
            'Pola redeem reward berulang melebihi batas normal',
            'Akun baru dengan akumulasi poin tinggi dalam 7 hari',
            'Device fingerprint cocok dengan member yang pernah diflag',
        ];

        return [
            'member_1_id' => Member::factory(),
            'member_2_id' => Member::factory(),
            'confidence_score' => fake()->randomFloat(2, 55, 98),
            'reason' => fake()->randomElement($reasons),
            'is_resolved' => fake()->boolean(30),
        ];
    }
}
