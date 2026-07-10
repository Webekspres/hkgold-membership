<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationPlatform;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->count() === 0) {
            $this->call(UserSeeder::class);
        }

        if (Notification::query()->exists()) {
            return;
        }

        $users = User::query()->take(5)->get();

        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $notificationKey = (string) Str::uuid();
                $title = 'Notifikasi Promo #'.($i + 1);
                $body = 'Dapatkan promo spesial untuk pembelian hari ini.';

                Notification::factory()->create([
                    'user_id' => $user->id,
                    'notification_key' => $notificationKey,
                    'title' => $title,
                    'body' => $body,
                    'platform' => NotificationPlatform::WebAdminInApp,
                    'status' => NotificationDeliveryStatus::Sent,
                    'sent_at' => now(),
                    'read_at' => fake()->boolean(50) ? now() : null,
                ]);

                Notification::factory()->create([
                    'user_id' => $user->id,
                    'notification_key' => $notificationKey,
                    'title' => $title,
                    'body' => $body,
                    'platform' => NotificationPlatform::MobileAppPush,
                    'status' => NotificationDeliveryStatus::Pending,
                    'sent_at' => null,
                    'read_at' => null,
                ]);
            }
        }
    }
}
