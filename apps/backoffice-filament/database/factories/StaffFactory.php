<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Staff $staff): void {
            if ($staff->id !== null) {
                return;
            }

            $user = User::factory()->staffRole(
                fake()->randomElement([Role::StoreManager, Role::Marketing, Role::Executive])
            )->create();
            $staff->setAttribute('id', $user->id);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'allowed_ip' => fake()->optional(0.6)->ipv4(),
            'is_device_approved' => fake()->boolean(70),
        ];
    }
}
