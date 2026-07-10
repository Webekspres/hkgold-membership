<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationPlatform;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(NotificationPlatform::cases());
        $status = $platform === NotificationPlatform::WebAdminInApp
            ? fake()->randomElement([NotificationDeliveryStatus::Sent, NotificationDeliveryStatus::Pending])
            : fake()->randomElement([
                NotificationDeliveryStatus::Pending,
                NotificationDeliveryStatus::Sent,
                NotificationDeliveryStatus::Failed,
            ]);

        $now = now();

        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'notification_key' => (string) Str::uuid(),
            'title' => fake()->sentence(5),
            'body' => fake()->paragraph(2),
            'platform' => $platform,
            'status' => $status,
            'data_payload' => [
                'screen' => fake()->randomElement(['home', 'rewards', 'transactions']),
                'reference_id' => (string) Str::uuid(),
            ],
            'read_at' => $platform === NotificationPlatform::WebAdminInApp && fake()->boolean(30)
                ? $now->copy()->subMinutes(fake()->numberBetween(1, 120))
                : null,
            'sent_at' => $status === NotificationDeliveryStatus::Sent
                ? $now->copy()->subMinutes(fake()->numberBetween(1, 60))
                : null,
            'failed_at' => $status === NotificationDeliveryStatus::Failed
                ? $now->copy()->subMinutes(fake()->numberBetween(1, 60))
                : null,
            'error_message' => $status === NotificationDeliveryStatus::Failed
                ? fake()->sentence()
                : null,
            'attempt_count' => $status === NotificationDeliveryStatus::Failed
                ? fake()->numberBetween(1, 5)
                : fake()->numberBetween(0, 2),
        ];
    }
}
