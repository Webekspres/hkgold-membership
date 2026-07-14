<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationCampaign;
use Illuminate\Database\Seeder;

class NotificationCampaignSeeder extends Seeder
{
    public function run(): void
    {
        if (NotificationCampaign::query()->exists()) {
            return;
        }

        NotificationCampaign::factory()->count(5)->create();
    }
}
