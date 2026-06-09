<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PointMutation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointMutationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        PointMutation::factory()->count(15)->create();
    }
}
