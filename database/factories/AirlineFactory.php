<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Airline>
 */
class AirlineFactory extends Factory
{
    protected $model = Airline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'iata_code' => strtoupper(fake()->unique()->lexify('??')),
            'icao_code' => strtoupper(fake()->unique()->lexify('???')),
            'country' => fake()->country(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'description' => fake()->catchPhrase(),
            'active' => fake()->boolean(80),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => true,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
            ];
        });
    }
}
