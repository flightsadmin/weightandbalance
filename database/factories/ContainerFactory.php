<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['baggage', 'cargo']);

        return [
            'flight_id' => null,
            'container_number' => strtoupper(fake()->bothify('??#####??')),
            'type' => $type,
            'compartment' => null,
            'status' => null,
            'tare_weight' => fake()->numberBetween(60, 85),
            'weight' => 0,
            'max_weight' => 2000,
            'notes' => fake()->optional(15)->realText(50),
        ];
    }
}
