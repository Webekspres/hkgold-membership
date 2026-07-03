<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\TierStatus;
use App\Models\Address;
use App\Models\Branch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Address::query()->count() === 0) {
            $this->call(AddressSeeder::class);
        }

        if (Branch::query()->count() === 0) {
            $this->call(BranchSeeder::class);
        }

        $addressIds = Address::query()->pluck('id')->all();
        $branchIds = Branch::query()->pluck('id')->all();

        if (! Member::query()->where('member_number', 'HKD0000001')->exists()) {
            Member::factory()->create([
                'member_number' => 'HKD0000001',
                'address_id' => $addressIds[0] ?? null,
                'registered_at_branch_id' => Branch::query()->where('branch_code', 'HK01')->value('id'),
                'current_tier' => TierStatus::Silver,
                'point_balance' => 0,
                'highest_point' => 0,
                'is_suspended' => false,
            ]);
        }

        $customerUsers = User::query()
            ->where('role', Role::Member)
            ->whereDoesntHave('member')
            ->get();

        if ($customerUsers->isEmpty()) {
            $this->call(UserSeeder::class);
            $customerUsers = User::query()
                ->where('role', Role::Member)
                ->whereDoesntHave('member')
                ->get();
        }

        foreach ($customerUsers->values() as $index => $user) {
            Member::factory()->create([
                'user_id' => $user->id,
                'address_id' => $addressIds[$index % count($addressIds)],
                'registered_at_branch_id' => $branchIds[$index % count($branchIds)],
            ]);
        }

        $remaining = 10 - Member::query()->count();

        if ($remaining > 0) {
            Member::factory()
                ->count($remaining)
                ->create(fn (): array => [
                    'address_id' => fake()->randomElement($addressIds),
                    'registered_at_branch_id' => fake()->randomElement($branchIds),
                ]);
        }
    }
}
