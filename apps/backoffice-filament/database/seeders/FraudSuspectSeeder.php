<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FraudSuspect;
use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FraudSuspectSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Member::query()->count() < 2) {
            $this->call(MemberSeeder::class);
        }

        $members = Member::query()->limit(20)->pluck('id')->all();

        for ($i = 0; $i < 10; $i++) {
            $pair = fake()->randomElements($members, 2);
            FraudSuspect::factory()->create([
                'member_1_id' => $pair[0],
                'member_2_id' => $pair[1],
            ]);
        }
    }
}
