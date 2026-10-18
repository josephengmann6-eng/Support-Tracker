<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'description' => fake()->sentence(),
            'is_active' => true,
            'created_at' => now()->subDays(30),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
