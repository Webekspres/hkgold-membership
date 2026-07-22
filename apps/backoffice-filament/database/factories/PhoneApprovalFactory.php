<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Enums\ChangePhoneSource;
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
        $source = fake()->randomElement(ChangePhoneSource::cases());

        return [
            'member_id' => Member::query()->inRandomOrder()->value('id') ?? Member::factory(),
            'requested_by_id' => $source === ChangePhoneSource::AdminAssisted
                ? (Staff::query()->inRandomOrder()->value('id') ?? Staff::factory())
                : null,
            'approved_by_id' => $status === ApprovalStatus::Approved
                ? (Staff::query()->inRandomOrder()->value('id') ?? Staff::factory())
                : null,
            'old_phone_number' => '08'.fake()->numerify('##########'),
            'new_phone_number' => '08'.fake()->unique()->numerify('##########'),
            'source' => $source,
            'status' => $status,
            'reason' => fake()->optional()->sentence(),
            'action_notes' => in_array($status, [ApprovalStatus::Approved, ApprovalStatus::Rejected], true)
                ? fake()->sentence()
                : null,
            'processed_at' => in_array($status, [ApprovalStatus::Approved, ApprovalStatus::Rejected, ApprovalStatus::Cancelled], true)
                ? now()
                : null,
        ];
    }
}
