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
        $usedProfilePhotoIds = User::query()
            ->whereNotNull('profile_photo_id')
            ->pluck('profile_photo_id')
            ->all();

        $availablePhotoIds = array_values(array_diff($mediaIds, $usedProfilePhotoIds));

        $superAdminUser = $this->seedFixedUser(
            email: 'superadmin@example.com',
            role: Role::SuperAdmin,
            fullName: 'Super Admin HK Gold',
            profilePhotoId: array_shift($availablePhotoIds),
        );

        $this->assignShieldRoleByName($superAdminUser, strtolower(Role::SuperAdmin->value));

        $administratorUser = $this->seedFixedUser(
            email: 'administrator@example.com',
            role: Role::Administrator,
            fullName: 'Administrator HK Gold',
            profilePhotoId: array_shift($availablePhotoIds),
        );

        $this->assignShieldRoleByName($administratorUser, Utils::getSuperAdminName());

        if (User::query()->where('role', '!=', Role::Member)->whereNotIn('email', [
            'superadmin@example.com',
            'administrator@example.com',
        ])->count() > 0) {
            return;
        }

        User::factory()->count(3)->staffRole(Role::StoreManager)->create()
            ->each(fn (User $user): User => $this->assignShieldRole($user));
        User::factory()->count(2)->staffRole(Role::Marketing)->create()
            ->each(fn (User $user): User => $this->assignShieldRole($user));

        $memberUsedPhotoIds = User::query()
            ->whereNotNull('profile_photo_id')
            ->pluck('profile_photo_id')
            ->all();

        User::factory()
            ->count(12)
            ->member()
            ->create(function () use ($mediaIds, &$memberUsedPhotoIds): array {
                $available = array_values(array_diff($mediaIds, $memberUsedPhotoIds));

                if ($available === []) {
                    return ['profile_photo_id' => null];
                }

                $profilePhotoId = fake()->randomElement($available);
                $memberUsedPhotoIds[] = $profilePhotoId;

                return ['profile_photo_id' => $profilePhotoId];
            })
            ->each(fn (User $user): User => $this->assignShieldRole($user));
    }

    private function seedFixedUser(
        string $email,
        Role $role,
        string $fullName,
        ?string $profilePhotoId,
    ): User {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'password' => 'password123',
                'full_name' => $fullName,
                'role' => $role,
                'profile_photo_id' => $profilePhotoId,
                'is_active' => true,
            ],
        );
    }

    private function assignShieldRoleByName(User $user, string $roleName): User
    {
        $shieldRole = SpatieRole::query()->where('name', $roleName)->first();

        if ($shieldRole !== null && ! $user->hasRole($shieldRole)) {
            $user->syncRoles([$shieldRole]);
        }

        return $user;
    }

    private function assignShieldRole(User $user): User
    {
        if (! $user->role instanceof Role) {
            return $user;
        }

        return $this->assignShieldRoleByName($user, strtolower($user->role->value));
    }
}
