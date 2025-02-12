<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\CrewSeating;
use App\Models\CrewDistribution;
use Illuminate\Database\Seeder;

class CrewSeatingSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftTypes = AircraftType::all();

        foreach ($aircraftTypes as $type) {
            $seatingLocations = [
                [
                    'name' => 'FWD of FWD door',
                    'max_seats' => 2,
                    'arm_length' => -11.520,
                    'index_per_kg' => -0.01152,
                ],
                [
                    'name' => 'FWD of aft door RH',
                    'max_seats' => 1,
                    'arm_length' => 12.991,
                    'index_per_kg' => 0.01299,
                ],
                [
                    'name' => 'FWD of aft door LH',
                    'max_seats' => 1,
                    'arm_length' => 12.991,
                    'index_per_kg' => 0.01299,
                ],
                [
                    'name' => 'Aft of aft door',
                    'max_seats' => 1,
                    'arm_length' => 13.665,
                    'index_per_kg' => 0.01366,
                ],
            ];

            foreach ($seatingLocations as $location) {
                CrewSeating::updateOrCreate(
                    [
                        'aircraft_type_id' => $type->id,
                        'location' => $location['name'],
                    ],
                    [
                        'max_number' => $location['max_seats'],
                        'arm' => $location['arm_length'],
                        'index_per_kg' => $location['index_per_kg'],
                    ]
                );
            }
            $distributions = [
                1 => [1, 0, 0, 0],
                2 => [1, 0, 0, 1],
                3 => [2, 0, 0, 1],
                4 => [2, 0, 1, 1],
                5 => [2, 1, 1, 1],
            ];

            foreach ($distributions as $crewCount => $seatDistribution) {
                CrewDistribution::create([
                    'aircraft_type_id' => $type->id,
                    'crew_count' => $crewCount,
                    'distribution' => $seatDistribution,
                ]);
            }
        }
    }
}