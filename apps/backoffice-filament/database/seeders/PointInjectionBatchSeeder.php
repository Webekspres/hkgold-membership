<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PointInjectionBatch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointInjectionBatchSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        PointInjectionBatch::factory()->count(10)->withDetails(15)->create();
    }
}
