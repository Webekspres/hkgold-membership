<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MemberAnomaly;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberAnomalySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Member::query()->count() === 0) {
            $this->call(MemberSeeder::class);
        }

        MemberAnomaly::factory()->count(10)->create();
    }
}
