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
        static $seatNumber = 1;
        $row = ceil($seatNumber / 6); // 6 seats per row (A-F)
        $letter = chr(65 + (($seatNumber - 1) % 6)); // A through F
        $currentSeat = $row.$letter;
        $seatNumber++;

        return [
            'flight_id' => null,
            'name' => fake()->name(),
            'type' => fake()->randomElement(['male', 'female', 'child', 'infant']),
            'seat_number' => $currentSeat,
            'ticket_number' => strtoupper(fake()->bothify('#############')),
            'acceptance_status' => fake()->randomElement(['booked', 'accepted', 'standby', 'offloaded']),
            'boarding_status' => fake()->randomElement(['boarding', 'boarding', 'unboarded']),
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }

    public function booked()
    {
        return $this->state(function (array $attributes) {
            return [
                'acceptance_status' => 'booked',
            ];
        });
    }

    public function accepted()
    {
        return $this->state(function (array $attributes) {
            return [
                'acceptance_status' => 'accepted',
            ];
        });
    }

    public function standby()
    {
        return $this->state(function (array $attributes) {
            return [
                'acceptance_status' => 'standby',
            ];
        });
    }

    public function offloaded()
    {
        return $this->state(function (array $attributes) {
            return [
                'acceptance_status' => 'offloaded',
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
}
