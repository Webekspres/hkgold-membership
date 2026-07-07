<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Media;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Media::query()->count() === 0) {
            $this->call(MediaSeeder::class);
        }

        $mediaIds = Media::query()->pluck('id')->all();
        $usedProfilePhotoIds = [];

        $superAdminPhotoId = $mediaIds[0] ?? null;

        $superAdmin = User::factory()->superAdmin()->create([
            'profile_photo_id' => $superAdminPhotoId,
        ]);

        $shieldSuperAdminRole = SpatieRole::query()
            ->where('name', Utils::getSuperAdminName())
            ->first();

        if ($shieldSuperAdminRole !== null) {
            $superAdmin->assignRole($shieldSuperAdminRole);
        }

        if ($superAdminPhotoId !== null) {
            $usedProfilePhotoIds[] = $superAdminPhotoId;
        }

        User::factory()->count(1)->staffRole(Role::Administrator)->create()
            ->each(fn (User $user): User => $this->assignShieldRole($user));
        User::factory()->count(3)->staffRole(Role::StoreManager)->create()
            ->each(fn (User $user): User => $this->assignShieldRole($user));
        User::factory()->count(2)->staffRole(Role::Marketing)->create()
            ->each(fn (User $user): User => $this->assignShieldRole($user));

        User::factory()
            ->count(12)
            ->member()
            ->create(function () use ($mediaIds, &$usedProfilePhotoIds): array {
                $profilePhotoId = null;

                if (fake()->boolean(30)) {
                    $available = array_values(array_diff($mediaIds, $usedProfilePhotoIds));

                    if ($available !== []) {
                        $profilePhotoId = fake()->randomElement($available);
                        $usedProfilePhotoIds[] = $profilePhotoId;
                    }
                }

                return ['profile_photo_id' => $profilePhotoId];
            })
            ->each(fn (User $user): User => $this->assignShieldRole($user));
    }

    private function assignShieldRole(User $user): User
    {
        if (! $user->role instanceof Role) {
            return $user;
        }

        $shieldRole = SpatieRole::query()
            ->where('name', strtolower($user->role->value))
            ->first();

        if ($shieldRole !== null) {
            $user->assignRole($shieldRole);
        }

        return $user;
    }
}
