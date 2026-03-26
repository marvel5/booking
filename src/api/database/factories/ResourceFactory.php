<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(100, 999);

        return [
            'name' => "Apartament {$number}",
            'description' => fake()->sentence(),
            'capacity' => fake()->randomElement([1, 2, 2, 4]),
        ];
    }

    public function room(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Stolik ' . fake()->unique()->numberBetween(1, 50),
            'description' => fake()->sentence(),
            'capacity' => fake()->randomElement([2, 4, 6]),
        ]);
    }
}
