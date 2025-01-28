<?php

namespace Database\Factories;

use App\Models\Hold;
use App\Models\AircraftType;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoldFactory extends Factory
{
    protected $model = Hold::class;

    public function definition(): array
    {
        $holdTypes = [
            'FH' => 'Forward Hold',
            'AH' => 'Aft Hold',
            'BH' => 'Bulk Hold'
        ];

        $code = $this->faker->randomElement(array_keys($holdTypes));

        return [
            'aircraft_type_id' => null,
            'name' => $holdTypes[$code],
            'code' => $code,
            'position' => $this->faker->numberBetween(1, 4),
            'max_weight' => $this->faker->numberBetween(2000, 5000),
            'is_active' => fake()->boolean(90),
        ];
    }

    public function forwardHold()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Forward Hold',
                'code' => 'FH',
                'position' => 1,
            ];
        });
    }

    public function aftHold()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Aft Hold',
                'code' => 'AH',
                'position' => 2,
            ];
        });
    }

    public function bulkHold()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Bulk Hold',
                'code' => 'BH',
                'position' => 3,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}