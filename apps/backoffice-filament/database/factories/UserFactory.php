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
            'phone' => '08'.fake()->numerify('##########'),
            'password' => 'password123',
            'name' => fake('id_ID')->name(),
            'role' => Role::Customer,
            'profile_photo_id' => null,
            'is_active' => true,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => [
            'role' => Role::SuperAdmin,
            'name' => 'Admin HK Gold VIP',
            'email' => 'admin@example.com',
            'phone' => '081100000001',
        ]);
    }

    public function staffRole(Role $role = Role::StoreManager): static
    {
        return $this->state(fn (): array => [
            'role' => $role,
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (): array => [
            'role' => Role::Customer,
        ]);
    }
}
