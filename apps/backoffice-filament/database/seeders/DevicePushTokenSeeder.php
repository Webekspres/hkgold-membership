<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DevicePushTokenPlatform;
use App\Models\DevicePushToken;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DevicePushTokenSeeder extends Seeder
{
    public function run(): void
    {
        if (DevicePushToken::query()->exists()) {
            return;
        }

        $members = Member::query()
            ->whereNotNull('user_id')
            ->where('is_suspended', false)
            ->with('user')
            ->limit(20)
            ->get();

        foreach ($members as $member) {
            if ($member->user === null) {
                continue;
            }

            $this->seedTokenForUser($member->user, DevicePushTokenPlatform::Mobile);
        }

        $staffUsers = User::query()
            ->whereHas('staff')
            ->limit(5)
            ->get();

        foreach ($staffUsers as $user) {
            $this->seedTokenForUser($user, DevicePushTokenPlatform::Web);
        }
    }

    private function seedTokenForUser(User $user, DevicePushTokenPlatform $platform): void
    {
        DevicePushToken::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'token' => sprintf(
                    'dummy_%s_%s',
                    strtolower($platform->value),
                    Str::limit($user->id, 8, ''),
                ),
            ],
            [
                'device_uuid' => (string) Str::uuid(),
                'platform' => $platform,
                'last_used_at' => null,
                'revoked_at' => null,
            ],
        );
    }
}
