<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DevicePushTokenPlatform;
use App\Models\DevicePushToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DevicePushToken>
 */
class DevicePushTokenFactory extends Factory
{
    protected $model = DevicePushToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'device_uuid' => (string) Str::uuid(),
            'platform' => DevicePushTokenPlatform::Mobile,
            'token' => 'fcm_dummy_'.fake()->regexify('[A-Za-z0-9]{120}'),
            'last_used_at' => null,
            'revoked_at' => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'revoked_at' => now(),
        ]);
    }
}
