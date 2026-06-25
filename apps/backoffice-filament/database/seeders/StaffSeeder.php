<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Branch::query()->count() === 0) {
            $this->call(BranchSeeder::class);
        }

        if (User::query()->whereIn('role', [
            Role::Administrator,
            Role::SuperAdmin,
            Role::Marketing,
            Role::StoreManager,
        ])->count() === 0) {
            $this->call(UserSeeder::class);
        }

        $branchIds = Branch::query()->pluck('id')->all();

        $staffUsers = User::query()
            ->whereIn('role', [
                Role::Administrator,
                Role::SuperAdmin,
                Role::Marketing,
                Role::StoreManager,
            ])
            ->whereDoesntHave('staff')
            ->get();

        foreach ($staffUsers as $index => $user) {
            Staff::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'branch_id' => $branchIds[$index % count($branchIds)],
                    'employee_code' => 'EMP'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                ],
            );
        }

        if (Staff::query()->count() < 10) {
            Staff::factory()->count(10 - Staff::query()->count())->create();
        }
    }
}
