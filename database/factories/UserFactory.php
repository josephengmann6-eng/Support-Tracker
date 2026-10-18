<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'staff_id' => 'STF-'.fake()->unique()->numerify('####'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('024#######'),
            'department' => 'Application Support',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => User::ROLE_ADMIN]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
