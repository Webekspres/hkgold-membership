<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Models\Member;
use App\Models\PhoneApproval;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhoneApproval>
 */
class PhoneApprovalFactory extends Factory
{
    protected $model = PhoneApproval::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(ApprovalStatus::cases());

        return [
            'member_id' => Member::query()->inRandomOrder()->value('id') ?? Member::factory(),
            'requested_by_id' => Staff::query()->inRandomOrder()->value('id') ?? Staff::factory(),
            'approved_by_id' => $status === ApprovalStatus::Approved
                ? (Staff::query()->inRandomOrder()->value('id') ?? Staff::factory())
                : null,
            'old_phone_number' => '08'.fake()->numerify('##########'),
            'new_phone_number' => '08'.fake()->unique()->numerify('##########'),
            'status' => $status,
            'reason' => fake()->optional()->sentence(),
            'action_notes' => $status !== ApprovalStatus::Pending ? fake()->sentence() : null,
            'processed_at' => $status !== ApprovalStatus::Pending ? now() : null,
        ];
    }
}
