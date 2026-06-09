<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Address;
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

        $addressIds = Address::query()->pluck('id')->all();

        $customerUsers = User::query()
            ->where('role', Role::Customer)
            ->whereDoesntHave('member')
            ->get();

        if ($customerUsers->isEmpty()) {
            $this->call(UserSeeder::class);
            $customerUsers = User::query()
                ->where('role', Role::Customer)
                ->whereDoesntHave('member')
                ->get();
        }

        foreach ($customerUsers->values() as $index => $user) {
            Member::factory()->create([
                'id' => $user->id,
                'address_id' => $addressIds[$index % count($addressIds)],
            ]);
        }

        $remaining = 10 - Member::query()->count();

        if ($remaining > 0) {
            Member::factory()
                ->count($remaining)
                ->create(fn (): array => [
                    'address_id' => fake()->randomElement($addressIds),
                ]);
        }
    }
}
