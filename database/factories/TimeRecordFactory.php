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
        $timezone = new \DateTimeZone(config('app.timezone'));
        $recordedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $recordedAt->setTimezone($timezone);
        
        return [
            'user_id' => User::factory(),
            'recorded_at' => $recordedAt,
        ];
    }

    /**
     * Create a record for today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => now(config('app.timezone')),
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
