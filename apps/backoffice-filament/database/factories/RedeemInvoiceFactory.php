<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Member;
use App\Models\RedeemInvoice;
use App\Models\Reward;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RedeemInvoice>
 */
class RedeemInvoiceFactory extends Factory
{
    protected $model = RedeemInvoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['COMPLETED', 'REFUNDED']);

        return [
            'invoice_number' => 'RDM-'.fake()->unique()->numerify('##########'),
            'member_id' => Member::query()->inRandomOrder()->value('id') ?? Member::factory(),
            'staff_id' => Staff::query()->inRandomOrder()->value('id') ?? Staff::factory(),
            'branch_id' => Branch::query()->inRandomOrder()->value('id') ?? Branch::factory(),
            'reward_id' => Reward::query()->inRandomOrder()->value('id') ?? Reward::factory(),
            'points_redeemed' => fake()->numberBetween(15_000, 200_000),
            'status' => $status,
        ];
    }
}
