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
            Role::StoreManager,
            Role::Marketing,
            Role::Executive,
            Role::SuperAdmin,
        ])->count() === 0) {
            $this->call(UserSeeder::class);
        }

        $branchIds = Branch::query()->pluck('id')->all();

        $staffUsers = User::query()
            ->whereIn('role', [Role::StoreManager, Role::Marketing, Role::Executive, Role::SuperAdmin])
            ->whereDoesntHave('staff')
            ->get();

        foreach ($staffUsers as $index => $user) {
            Staff::query()->firstOrCreate(
                ['id' => $user->id],
                [
                    'branch_id' => $branchIds[$index % count($branchIds)],
                    'allowed_ip' => fake()->optional(0.5)->ipv4(),
                    'is_device_approved' => $user->role !== Role::SuperAdmin,
                ],
            );
        }

        if (Staff::query()->count() < 10) {
            Staff::factory()->count(10 - Staff::query()->count())->create();
        }
    }
}
