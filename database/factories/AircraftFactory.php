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
            'manufacture_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'serial_number' => fake()->unique()->numerify('######'),
            'active' => fake()->boolean(90), // 90% chance of being active
            'remarks' => fake()->sentence(),
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

    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
            ];
        });
    }
}
