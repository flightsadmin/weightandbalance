<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Passenger>
 */
class PassengerFactory extends Factory
{
    protected $model = Passenger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flight_id' => null,
            'seat_id' => null,
            'name' => fake()->name(),
            'type' => fake()->randomElement(['male', 'female', 'child', 'infant']),
            'ticket_number' => strtoupper(fake()->bothify('#############')),
            'acceptance_status' => fake()->randomElement(['booked', 'accepted', 'standby', 'offloaded']),
            'boarding_status' => fake()->randomElement(['boarding', 'boarding', 'unboarded']),
        ];
    }

    public function forFlight(Flight $flight)
    {
        return $this->state(function (array $attributes) use ($flight) {
            return [
                'flight_id' => $flight->id,
            ];
        });
    }
}
