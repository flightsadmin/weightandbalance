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
        $type = fake()->randomElement(['male', 'female', 'child']);
        $attributes = [
            'wchr' => false,
            'wchs' => false,
            'wchc' => false,
            'exst' => false,
            'stcr' => false,
            'deaf' => false,
            'blind' => false,
            'dpna' => false,
            'meda' => false,
            'infant' => false,
            'infant_name' => null,
        ];

        return [
            'flight_id' => null,
            'name' => fake()->name(),
            'type' => $type,
            'pnr' => strtoupper(fake()->bothify('??###?')),
            'ticket_number' => fake()->numerify('###-##########'),
            'acceptance_status' => fake()->randomElement(['pending', 'accepted', 'rejected']),
            'boarding_status' => 'pending',
            'attributes' => $attributes,
        ];
    }

    public function infant(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'infant',
                'attributes' => array_merge($attributes['attributes'] ?? [], [
                    'infant' => true,
                    'infant_name' => fake()->name(),
                ]),
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Passenger $passenger) {
            // If this is an infant, find or create an adult on the same flight
            if ($passenger->type === 'infant') {
                // Try to find an existing adult passenger without an infant
                $adult = Passenger::where('flight_id', $passenger->flight_id)
                    ->whereIn('type', ['male', 'female'])
                    ->whereJsonDoesntContain('attributes->infant', true)
                    ->inRandomOrder()
                    ->first();

                // If no adult found, create one
                if (!$adult) {
                    $adult = Passenger::factory()->state([
                        'flight_id' => $passenger->flight_id,
                        'type' => fake()->randomElement(['male', 'female']),
                    ])->create();
                }

                // Update adult's attributes to include infant info
                $attributes = $adult->attributes;
                $attributes['infant'] = true;
                $attributes['infant_name'] = $passenger->name;
                $adult->update(['attributes' => $attributes]);
            }
            // If this is an adult, randomly assign an infant
            elseif (in_array($passenger->type, ['male', 'female']) && fake()->boolean(20)) {
                Passenger::factory()->infant()->create([
                    'flight_id' => $passenger->flight_id,
                ]);
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
