<?php

namespace Database\Factories;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['baggage', 'cargo']);

        return [
            'flight_id' => null,
            'container_number' => null,
            'type' => $type,
            'position_id' => null,
            'status' => null,
            'tare_weight' => fake()->numberBetween(60, 85),
            'weight' => 0,
            'max_weight' => 2000,
            'notes' => fake()->optional(15)->realText(50),
        ];
    }

    public function forFlight(Flight $flight)
    {
        $uld = fake()->randomElement(['AKE', 'PMC', 'PLA']);

        return $this->state(function (array $attributes) use ($flight, $uld) {
            return [
                'flight_id' => $flight->id,
                'container_number' => $uld . strtoupper(fake()->bothify('#####')) . $flight->airline->iata_code,
            ];
        });
    }
}
