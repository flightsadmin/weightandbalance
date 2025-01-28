<?php

namespace Database\Factories;

use App\Models\Hold;
use App\Models\HoldPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoldPositionFactory extends Factory
{
    protected $model = HoldPosition::class;

    public function definition(): array
    {
        $row = $this->faker->numberBetween(1, 4);
        $sides = ['L', 'R'];
        $side = $this->faker->randomElement($sides);

        return [
            'hold_id' => null,
            'code' => $row . ($side ?? ''),
            'row' => $row,
            'side' => $side,
            'max_weight' => $this->faker->numberBetween(1000, 2000),
            'is_active' => true,
        ];
    }

    public function leftSide()
    {
        return $this->state(function (array $attributes) {
            return [
                'side' => 'L',
                'code' => $attributes['row'] . 'L',
            ];
        });
    }

    public function rightSide()
    {
        return $this->state(function (array $attributes) {
            return [
                'side' => 'R',
                'code' => $attributes['row'] . 'R',
            ];
        });
    }

    public function center()
    {
        return $this->state(function (array $attributes) {
            return [
                'side' => null,
                'code' => (string) $attributes['row'],
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

    public function forRow($row)
    {
        return $this->state(function (array $attributes) use ($row) {
            $side = $attributes['side'] ?? '';
            return [
                'row' => $row,
                'code' => $row . $side,
            ];
        });
    }
}