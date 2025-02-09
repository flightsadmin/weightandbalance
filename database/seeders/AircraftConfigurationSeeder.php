<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\Airline;
use App\Models\CabinZone;
use App\Models\Envelope;
use App\Models\Hold;
use App\Models\Passenger;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class AircraftConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftType = AircraftType::with([
            'aircraft.flights.passengers',
            'cabinZones',
            'seats'
        ])->inRandomOrder()->first();
        $airline = Airline::inRandomOrder()->first();

        $settings = [
            'ref_sta_at' => ['value' => '0.0', 'type' => 'float', 'description' => 'Reference station (meters)'],
            'k_constant' => ['value' => '100.0', 'type' => 'float', 'description' => 'K constant for index calculation'],
            'c_constant' => ['value' => '500.0', 'type' => 'float', 'description' => 'C constant for index calculation'],
            'length_of_mac' => ['value' => '4.194', 'type' => 'float', 'description' => 'Length of MAC (meters)'],
            'lemac_at' => ['value' => '16.86', 'type' => 'float', 'description' => 'Leading Edge MAC (meters)'],
            'standard_cockpit_crew_weight' => ['value' => 85, 'type' => 'integer', 'description' => 'Standard crew weight'],
            'standard_cabin_crew_weight' => ['value' => 75, 'type' => 'integer', 'description' => 'Standard cabin crew weight'],
            'standard_passenger_weight' => ['value' => 84, 'type' => 'integer', 'description' => 'Standard passenger weight'],
            'standard_pantry_weight' => ['value' => 250, 'type' => 'integer', 'description' => 'Standard pantry weight'],
            'standard_baggage_weight' => ['value' => 20, 'type' => 'integer', 'description' => 'Standard baggage weight'],
        ];

        foreach ($settings as $key => $data) {
            $aircraftType->settings()->updateOrCreate(
                ['key' => $key, 'airline_id' => $airline->id],
                $data
            );
        }

        $pantries = [
            'a' => ['name' => 'Forward Galley', 'weight' => 250, 'index' => 0.8],
            'b' => ['name' => 'Mid Galley', 'weight' => 200, 'index' => 1.0],
            'c' => ['name' => 'Aft Galley', 'weight' => 250, 'index' => 1.2],
        ];

        foreach ($pantries as $code => $data) {
            $aircraftType->settings()->updateOrCreate(
                ['key' => "pantry_{$code}_name", 'airline_id' => $airline->id],
                ['value' => $data['name'], 'type' => 'string']
            );

            $aircraftType->settings()->updateOrCreate(
                ['key' => "pantry_{$code}_weight", 'airline_id' => $airline->id],
                ['value' => $data['weight'], 'type' => 'integer']
            );

            $aircraftType->settings()->updateOrCreate(
                ['key' => "pantry_{$code}_index", 'airline_id' => $airline->id],
                ['value' => $data['index'], 'type' => 'float']
            );
        }

        $zones = [
            ['name' => 'A', 'max_capacity' => 60, 'index' => 0.85, 'arm' => 12.5],
            ['name' => 'B', 'max_capacity' => 90, 'index' => 1.0, 'arm' => 16.8],
            ['name' => 'C', 'max_capacity' => 30, 'index' => 1.15, 'arm' => 21.2],
        ];

        // Keep track of the last row number used
        $lastRowNumber = 0;

        foreach ($zones as $key => $zoneData) {
            $zone = CabinZone::updateOrCreate(
                [
                    'aircraft_type_id' => $aircraftType->id,
                    'name' => $zoneData['name'],
                ],
                $zoneData
            );

            $zone->seats()->delete();
            $rows = ceil($zone->max_capacity / 6);
            $columns = ['A', 'B', 'C', 'D', 'E', 'F'];

            $seats = [];
            for ($row = 1; $row <= $rows; $row++) {
                $actualRow = $lastRowNumber + $row; // Use incremental row numbers
                foreach ($columns as $column) {
                    $seats[] = [
                        'aircraft_type_id' => $aircraftType->id,
                        'cabin_zone_id' => $zone->id,
                        'row' => $actualRow,
                        'column' => $column,
                        'designation' => $actualRow . $column,
                        'type' => 'economy',
                        'is_exit' => in_array($actualRow, [12, 13]), // Example exit rows
                        'is_blocked' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $zone->seats()->createMany($seats);
            $lastRowNumber += $rows;
        }

        foreach ($aircraftType->aircraft as $aircraft) {
            foreach ($aircraft->flights as $flight) {
                $availableSeats = $aircraftType->seats()
                    ->whereDoesntHave('passenger', function ($query) use ($flight) {
                        $query->where('flight_id', $flight->id);
                    })
                    ->where('is_blocked', false)
                    ->get()
                    ->pluck('id')
                    ->toArray();

                $flight->passengers()->whereNull('seat_id')->each(function ($passenger) use (&$availableSeats) {
                    if (empty($availableSeats)) {
                        return false;
                    }

                    $randomIndex = array_rand($availableSeats);
                    $seatId = $availableSeats[$randomIndex];
                    unset($availableSeats[$randomIndex]);

                    $passenger->update(['seat_id' => $seatId]);
                });
            }
        }

        $holds = [
            [
                'name' => 'Forward Hold',
                'code' => 'FH',
                'position' => 1,
                'max_weight' => 3402,
                'index' => 0.9001,
                'positions' => [
                    ['code' => '11L', 'row' => 11, 'side' => 'L', 'max_weight' => 1134, 'index' => 0.8501],
                    ['code' => '11R', 'row' => 11, 'side' => 'R', 'max_weight' => 1134, 'index' => 0.8501],
                    ['code' => '12L', 'row' => 12, 'side' => 'L', 'max_weight' => 1134, 'index' => 0.9501],
                    ['code' => '12R', 'row' => 12, 'side' => 'R', 'max_weight' => 1134, 'index' => 0.9501],
                    ['code' => '13L', 'row' => 13, 'side' => 'L', 'max_weight' => 1134, 'index' => 1.0501],
                    ['code' => '13R', 'row' => 13, 'side' => 'R', 'max_weight' => 1134, 'index' => 1.0501],
                ],
            ],
            [
                'name' => 'Aft Hold',
                'code' => 'AH',
                'position' => 2,
                'max_weight' => 2268,
                'index' => 1.2001,
                'positions' => [
                    ['code' => '41L', 'row' => 41, 'side' => 'L', 'max_weight' => 1134, 'index' => 1.1501],
                    ['code' => '41R', 'row' => 41, 'side' => 'R', 'max_weight' => 1134, 'index' => 1.1501],
                    ['code' => '42L', 'row' => 42, 'side' => 'L', 'max_weight' => 1134, 'index' => 1.2501],
                    ['code' => '42R', 'row' => 42, 'side' => 'R', 'max_weight' => 1134, 'index' => 1.2501],
                    ['code' => '43L', 'row' => 43, 'side' => 'L', 'max_weight' => 1134, 'index' => 1.3501],
                    ['code' => '43R', 'row' => 43, 'side' => 'R', 'max_weight' => 1134, 'index' => 1.3501],
                ],
            ],
            [
                'name' => 'Bulk Hold',
                'code' => 'BH',
                'position' => 3,
                'max_weight' => 2268,
                'index' => 1.2001,
                'positions' => [
                    ['code' => '51', 'row' => 51, 'side' => null, 'max_weight' => 1134, 'index' => 1.1501],
                    ['code' => '52', 'row' => 52, 'side' => null, 'max_weight' => 1134, 'index' => 1.2501],
                    ['code' => '53', 'row' => 53, 'side' => null, 'max_weight' => 1134, 'index' => 1.3501],
                ],
            ],
        ];

        foreach ($holds as $holdData) {
            $positions = $holdData['positions'];
            unset($holdData['positions']);

            $hold = Hold::updateOrCreate(
                [
                    'aircraft_type_id' => $aircraftType->id,
                    'code' => $holdData['code'],
                ],
                $holdData
            );
            $hold->positions()->delete();
            $hold->positions()->createMany($positions);
        }

        Envelope::updateOrCreate(
            [
                'aircraft_type_id' => $aircraftType->id,
                'name' => 'ZFW',
            ],
            [
                'points' => [
                    ['weight' => 42600, 'index' => 0.7],
                    ['weight' => 61000, 'index' => 0.8],
                    ['weight' => 77000, 'index' => 0.9],
                    ['weight' => 66000, 'index' => 1.0],
                    ['weight' => 42600, 'index' => 1.1],

                ],
                'is_active' => true,
            ]
        );
    }
}