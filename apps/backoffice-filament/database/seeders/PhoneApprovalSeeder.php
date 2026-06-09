<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PhoneApproval;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhoneApprovalSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        PhoneApproval::factory()->count(10)->create();
    }
}
