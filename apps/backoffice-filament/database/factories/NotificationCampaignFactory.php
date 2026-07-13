<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Enums\NotificationPlatform;
use App\Models\NotificationCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationCampaign>
 */
class NotificationCampaignFactory extends Factory
{
    protected $model = NotificationCampaign::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(CampaignStatus::cases());
        $targetedCount = fake()->numberBetween(50, 5000);

        return [
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(2),
            'platforms' => [NotificationPlatform::MobileAppPush->value],
            'criteria_json' => [
                'type' => 'all_active_members',
            ],
            'targeted_count' => $targetedCount,
            'accepted_count' => $status === CampaignStatus::Completed
                ? fake()->numberBetween((int) ($targetedCount * 0.8), $targetedCount)
                : null,
            'failed_count' => $status === CampaignStatus::Completed
                ? fake()->numberBetween(0, (int) ($targetedCount * 0.1))
                : null,
            'status' => $status,
            'error_message' => $status === CampaignStatus::Failed
                ? fake()->sentence()
                : null,
            'created_by_id' => User::query()->inRandomOrder()->value('id'),
        ];
    }
}
