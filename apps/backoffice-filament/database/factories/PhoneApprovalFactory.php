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
        $oldPhone = '08'.fake()->numerify('##########');
        $status = fake()->randomElement(ApprovalStatus::cases());

        return [
            'member_id' => Member::factory(),
            'old_phone' => $oldPhone,
            'new_phone' => '08'.fake()->numerify('##########'),
            'status' => $status,
            'approved_by' => $status === ApprovalStatus::Approved ? Staff::factory() : null,
        ];
    }
}
