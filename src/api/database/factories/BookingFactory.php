<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('now', '+30 days');
        $endAt = (clone $startAt)->modify('+' . fake()->numberBetween(1, 5) . ' days');

        return [
            'resource_id' => Resource::factory(),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'customer_name' => fake()->name(),
        ];
    }

    /**
     * State for a booking with a specific time slot (useful in tests).
     */
    public function from(string $startAt, string $endAt): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);
    }
}
