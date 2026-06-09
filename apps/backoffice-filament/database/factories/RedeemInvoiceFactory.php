<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Models\RedeemInvoice;
use App\Models\Reward;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $status = fake()->randomElement(InvoiceStatus::cases());
        $expiresAt = fake()->dateTimeBetween('+1 day', '+7 days');

        return [
            'invoice_number' => 'RDM-'.fake()->unique()->numerify('##########'),
            'member_id' => Member::factory(),
            'branch_id' => Branch::factory(),
            'reward_id' => Reward::factory(),
            'points_deducted' => fake()->numberBetween(15_000, 200_000),
            'status' => $status,
            'qr_token' => Str::uuid()->toString(),
            'expires_at' => $expiresAt,
            'qr_expires_at' => (clone $expiresAt)->modify('-1 hour'),
            'confirmed_by_id' => $status === InvoiceStatus::Confirmed ? Staff::factory() : null,
            'cancelled_by_id' => $status === InvoiceStatus::Cancelled ? Staff::factory() : null,
        ];
    }
}
