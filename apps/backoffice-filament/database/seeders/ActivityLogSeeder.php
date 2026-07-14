<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $userId = User::query()->value('id');

        if ($userId === null) {
            return;
        }

        ActivityLog::factory()->count(5)->create([
            'user_id' => $userId,
        ]);
    }
}
