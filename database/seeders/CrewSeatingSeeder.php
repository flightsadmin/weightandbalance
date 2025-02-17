<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\CrewDistribution;
use App\Models\CrewSeating;
use Illuminate\Database\Seeder;

class CrewSeatingSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftTypes = AircraftType::all();

        foreach ($aircraftTypes as $type) {
            $seatingLocations = [
                [
                    'position' => 'deck_crew',
                    'name' => 'Cockpit',
                    'max_seats' => 4,
                    'arm_length' => -13.410,
                    'index_per_kg' => -0.01341,
                ],
                [
                    'position' => 'cabin_crew',
                    'name' => 'FWD of FWD door',
                    'max_seats' => 2,
                    'arm_length' => -11.520,
                    'index_per_kg' => -0.01152,
                ],
                [
                    'position' => 'cabin_crew',
                    'name' => 'FWD of aft door RH',
                    'max_seats' => 1,
                    'arm_length' => 12.991,
                    'index_per_kg' => -0.01299,
                ],
                [
                    'position' => 'cabin_crew',
                    'name' => 'FWD of aft door LH',
                    'max_seats' => 1,
                    'arm_length' => 12.991,
                    'index_per_kg' => 0.01299,
                ],
                [
                    'position' => 'cabin_crew',
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
                        'position' => $location['position'],
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
                2 => [1, 1, 0, 0],
                3 => [2, 1, 0, 0],
                4 => [2, 1, 1, 0],
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
