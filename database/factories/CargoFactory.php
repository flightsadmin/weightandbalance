<?php

namespace Database\Factories;

use App\Models\Cargo;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

class CargoFactory extends Factory
{
    protected $model = Cargo::class;

    public function definition(): array
    {
        $cargoTypes = [
            'general' => ['min' => 50, 'max' => 1000],
            'perishable' => ['min' => 20, 'max' => 500],
            'dangerous_goods' => ['min' => 10, 'max' => 200],
            'live_animals' => ['min' => 30, 'max' => 300],
            'valuable' => ['min' => 5, 'max' => 100],
            'mail' => ['min' => 50, 'max' => 800]
        ];

        $type = fake()->randomElement(array_keys($cargoTypes));
        $weight = fake()->numberBetween($cargoTypes[$type]['min'], $cargoTypes[$type]['max']);
        $volume = $weight * fake()->randomFloat(2, 0.2, 0.5); // Approximate volume based on weight

        return [
            'flight_id' => null,
            'container_id' => null,
            'awb_number' => fake()->unique()->numerify('###-########'),
            'weight' => $weight,
            'volume' => $volume,
            'type' => $type,
            'status' => fake()->randomElement(['accepted', 'loaded', 'offloaded']),
            'description' => fake()->optional(10)->sentence(),
            'special_instructions' => fake()->optional(10)->sentence()
        ];
    }

    public function accepted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'accepted',
            ];
        });
    }

    public function loaded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'loaded',
            ];
        });
    }

    public function offloaded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'offloaded',
            ];
        });
    }

    public function forFlight(Flight $flight)
    {
        return $this->state(function (array $attributes) use ($flight) {
            return [
                'flight_id' => $flight->id,
            ];
        });
    }

    public function ofType(string $type)
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'type' => $type,
            ];
        });
    }

    public function inCompartment(string $compartment)
    {
        return $this->state(function (array $attributes) use ($compartment) {
            return [
                'compartment' => $compartment,
            ];
        });
    }

    public function dangerousGoods()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'dangerous_goods',
                'special_instructions' => 'Handle with care - Dangerous Goods',
            ];
        });
    }
}
