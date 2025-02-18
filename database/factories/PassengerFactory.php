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
            'name' => fake()->name(),
            'ticket_number' => strtoupper(fake()->bothify('###-##########')),
            'reservation_number' => strtoupper(fake()->bothify('#?##?#')),
            'type' => fake()->randomElement(['male', 'female', 'child', 'infant']),
            'acceptance_status' => 'pending',
            'boarding_status' => 'unboarded',
            'attributes' => [
                'wchr' => fake()->boolean(),
                'wchs' => fake()->boolean(),
                'wchc' => fake()->boolean(),
                'exst' => fake()->boolean(),
                'stcr' => fake()->boolean(),
                'deaf' => fake()->boolean(),
                'blind' => fake()->boolean(),
                'dpna' => fake()->boolean(),
                'meda' => fake()->boolean(),
                'infant' => fake()->boolean(),
                'infant_name' => fake()->name(),
            ],
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Passenger $passenger) {
            if ($passenger->flight) {
                $availableSeat = $passenger->flight->aircraft->type->seats()
                    ->whereDoesntHave('passenger', function ($query) use ($passenger) {
                        $query->where('flight_id', $passenger->flight_id);
                    })
                    ->whereDoesntHave('flights', function ($query) use ($passenger) {
                        $query->where('flights.id', $passenger->flight_id)
                            ->where('flight_seats.is_blocked', true);
                    })->inRandomOrder()->first();

                if ($availableSeat) {
                    $passenger->flight->seats()->firstOrCreate(
                        ['seat_id' => $availableSeat->id],
                        ['is_blocked' => false]
                    );
                    $passenger->update(['seat_id' => $availableSeat->id]);
                }
            }
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
