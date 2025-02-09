<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aircraft>
 */
class AircraftFactory extends Factory
{
    protected $model = Aircraft::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'airline_id' => null,
            'aircraft_type_id' => null,
            'registration_number' => strtoupper(fake()->unique()->lexify('??-????')),
            'basic_weight' => fake()->numberBetween(40000, 100000),
            'basic_index' => fake()->randomFloat(4, 45.0001, 50.9999),
            'active' => fake()->boolean(90),
            'remarks' => fake()->sentence(),
        ];
    }

    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
            ];
        });
    }
}
