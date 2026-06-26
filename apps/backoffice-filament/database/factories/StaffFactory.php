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
            if ($staff->user_id !== null) {
                return;
            }

            $user = User::factory()->staffRole(
                fake()->randomElement([
                    Role::Administrator,
                    Role::Marketing,
                    Role::StoreManager,
                ])
            )->create();
            $staff->setAttribute('user_id', $user->id);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $employeeIndex = 100;

        $employeeIndex++;

        $branchId = Branch::query()->inRandomOrder()->value('id');

        return [
            'branch_id' => $branchId ?? Branch::factory(),
            'employee_code' => 'EMP'.str_pad((string) $employeeIndex, 5, '0', STR_PAD_LEFT),
        ];
    }
}
