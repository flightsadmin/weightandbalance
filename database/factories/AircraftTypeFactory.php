<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AircraftType>
 */
class AircraftTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['code' => 'B738', 'name' => 'Boeing 737-800', 'manufacturer' => 'Boeing', 'max_passengers' => 189, 'mtow' => 79016, 'mzfw' => 69000, 'mlw' => 72000, 'empty' => 41413, 'cargo' => 20453, 'fuel' => 26020, 'range' => 3115, 'category' => 'Narrow-body'],
            ['code' => 'A320', 'name' => 'Airbus A320', 'manufacturer' => 'Airbus', 'max_passengers' => 180, 'mtow' => 78000, 'mzfw' => 68000, 'mlw' => 72000, 'empty' => 42600, 'cargo' => 16600, 'fuel' => 24210, 'range' => 3300, 'category' => 'Narrow-body'],
            ['code' => 'A321', 'name' => 'Airbus A321', 'manufacturer' => 'Airbus', 'max_passengers' => 220, 'mtow' => 93500, 'mzfw' => 83500, 'mlw' => 87500, 'empty' => 48500, 'cargo' => 23430, 'fuel' => 30030, 'range' => 3200, 'category' => 'Narrow-body'],
            ['code' => 'B77W', 'name' => 'Boeing 777-300ER', 'manufacturer' => 'Boeing', 'max_passengers' => 396, 'mtow' => 351533, 'mzfw' => 301533, 'mlw' => 339500, 'empty' => 167829, 'cargo' => 76918, 'fuel' => 181283, 'range' => 7370, 'category' => 'Wide-body'],
        ];

        $type = fake()->unique()->randomElement($types);

        return [
            'code' => $type['code'],
            'name' => $type['name'],
            'manufacturer' => $type['manufacturer'],
            'max_passengers' => $type['max_passengers'],
            'cargo_capacity' => $type['cargo'],
            'max_fuel_capacity' => $type['fuel'],
            'empty_weight' => $type['empty'],
            'max_zero_fuel_weight' => $type['mzfw'],
            'max_takeoff_weight' => $type['mtow'],
            'max_landing_weight' => $type['mlw'],
            'max_range' => $type['range'],
            'category' => $type['category'],
            'max_deck_crew' => 2,
            'max_cabin_crew' => 2,
        ];
    }
}