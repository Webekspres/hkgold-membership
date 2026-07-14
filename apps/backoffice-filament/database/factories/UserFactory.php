<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'full_name' => fake('id_ID')->name(),
            'role' => Role::Member,
            'profile_photo_id' => null,
            'is_active' => true,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => [
            'role' => Role::SuperAdmin,
            'full_name' => 'Super Admin HK Gold',
            'email' => 'superadmin@example.com',
        ]);
    }

    public function administrator(): static
    {
        return $this->state(fn (): array => [
            'role' => Role::Administrator,
            'full_name' => 'Administrator HK Gold',
            'email' => 'administrator@example.com',
        ]);
    }

    public function staffRole(Role $role = Role::StoreManager): static
    {
        return $this->state(fn (): array => [
            'role' => $role,
        ]);
    }

    public function member(): static
    {
        return $this->state(fn (): array => [
            'role' => Role::Member,
        ]);
    }

    /**
     * @deprecated Use member() instead.
     */
    public function customer(): static
    {
        return $this->member();
    }
}
