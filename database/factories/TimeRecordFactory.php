<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeRecord>
 */
class TimeRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Create a record for today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => now(),
        ]);
    }

    /**
     * Create a record for a specific user
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}
