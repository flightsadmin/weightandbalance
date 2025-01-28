<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Fuel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fuel>
 */
class FuelFactory extends Factory
{
    protected $model = Fuel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Base fuel calculations (in kg)
        $tripFuel = fake()->numberBetween(5000, 15000);
        $contingencyFuel = $tripFuel * 0.05; // 5% of trip fuel
        $alternateFuel = fake()->numberBetween(2000, 4000);
        $finalReserveFuel = fake()->numberBetween(1500, 2500);
        $taxiFuel = fake()->numberBetween(200, 400);
        $additionalFuel = fake()->optional(0.3)->numberBetween(500, 2000) ?? 0;

        $totalFuel = $tripFuel + $contingencyFuel + $alternateFuel +
            $finalReserveFuel + $taxiFuel + $additionalFuel;

        return [
            'flight_id' => null,
            'taxi_fuel' => $taxiFuel,
            'trip_fuel' => $tripFuel,
            'contingency_fuel' => $contingencyFuel,
            'alternate_fuel' => $alternateFuel,
            'final_reserve_fuel' => $finalReserveFuel,
            'additional_fuel' => $additionalFuel,
            'total_fuel' => $totalFuel,
            'notes' => fake()->optional(0.3)->sentence()
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

    public function withAdditionalFuel()
    {
        return $this->state(function (array $attributes) {
            $additionalFuel = fake()->numberBetween(500, 2000);
            return [
                'additional_fuel' => $additionalFuel,
                'total_fuel' => $attributes['total_fuel'] + $additionalFuel,
                'notes' => 'Additional fuel added for extended holding',
            ];
        });
    }

    public function shortHaul()
    {
        return $this->state(function (array $attributes) {
            $tripFuel = fake()->numberBetween(3000, 8000);
            $contingencyFuel = $tripFuel * 0.05;
            $alternateFuel = fake()->numberBetween(1500, 3000);
            $finalReserveFuel = 1500;
            $taxiFuel = fake()->numberBetween(200, 300);
            $totalFuel = $tripFuel + $contingencyFuel + $alternateFuel + $finalReserveFuel + $taxiFuel;

            return [
                'taxi_fuel' => $taxiFuel,
                'trip_fuel' => $tripFuel,
                'contingency_fuel' => $contingencyFuel,
                'alternate_fuel' => $alternateFuel,
                'final_reserve_fuel' => $finalReserveFuel,
                'additional_fuel' => 0,
                'total_fuel' => $totalFuel,
            ];
        });
    }

    public function longHaul()
    {
        return $this->state(function (array $attributes) {
            $tripFuel = fake()->numberBetween(50000, 100000);
            $contingencyFuel = $tripFuel * 0.05;
            $alternateFuel = fake()->numberBetween(5000, 8000);
            $finalReserveFuel = 2500;
            $taxiFuel = fake()->numberBetween(300, 500);
            $totalFuel = $tripFuel + $contingencyFuel + $alternateFuel + $finalReserveFuel + $taxiFuel;

            return [
                'taxi_fuel' => $taxiFuel,
                'trip_fuel' => $tripFuel,
                'contingency_fuel' => $contingencyFuel,
                'alternate_fuel' => $alternateFuel,
                'final_reserve_fuel' => $finalReserveFuel,
                'additional_fuel' => 0,
                'total_fuel' => $totalFuel,
            ];
        });
    }
}
