<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\Airline;
use App\Models\CabinZone;
use App\Models\Hold;
use Illuminate\Database\Seeder;

class AircraftConfigSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftType = AircraftType::with([
            'aircraft.flights.passengers',
            'cabinZones',
            'seats',
        ])->inRandomOrder()->first();
        $airline = Airline::inRandomOrder()->first();

        $settings = [
            'ref_sta_at' => ['value' => 18.850, 'type' => 'float', 'description' => 'Reference station (meters)'],
            'k_constant' => ['value' => 50, 'type' => 'integer', 'description' => 'K constant for index calculation'],
            'c_constant' => ['value' => 1000, 'type' => 'integer', 'description' => 'C constant for index calculation'],
            'length_of_mac' => ['value' => 4.194, 'type' => 'float', 'description' => 'Length of MAC (meters)'],
            'lemac_at' => ['value' => 17.8015, 'type' => 'float', 'description' => 'Leading Edge MAC (meters)'],
            'standard_cockpit_crew_weight' => ['value' => 85, 'type' => 'integer', 'description' => 'Standard crew weight'],
            'standard_cabin_crew_weight' => ['value' => 75, 'type' => 'integer', 'description' => 'Standard cabin crew weight'],
            'standard_pantry_weight' => ['value' => 250, 'type' => 'integer', 'description' => 'Standard pantry weight'],
            'standard_baggage_weight' => ['value' => 13, 'type' => 'integer', 'description' => 'Standard baggage weight'],
            'standard_passenger_weight' => ['value' => 84, 'type' => 'integer', 'description' => 'Standard passenger weight'],
            'standard_male_passenger_weight' => ['value' => 88, 'type' => 'integer', 'description' => 'Standard male passenger weight'],
            'standard_female_passenger_weight' => ['value' => 70, 'type' => 'integer', 'description' => 'Standard female passenger weight'],
            'standard_child_passenger_weight' => ['value' => 35, 'type' => 'integer', 'description' => 'Standard child passenger weight'],
            'standard_infant_passenger_weight' => ['value' => 10, 'type' => 'integer', 'description' => 'Standard infant passenger weight'],
            'standard_fuel_density' => ['value' => 0.89, 'type' => 'float', 'description' => 'Standard fuel density'],
        ];

        foreach ($settings as $key => $data) {
            $aircraftType->settings()->updateOrCreate(
                ['key' => $key, 'airline_id' => $airline->id],
                $data
            );
        }

        $pantries = [
            'a' => ['name' => 'Pantry A', 'weight' => 497, 'index' => +1.59],
            'e' => ['name' => 'Pantry E', 'weight' => 45, 'index' => +0.18],
            'empty' => ['name' => 'Empty', 'weight' => 0, 'index' => 0],
        ];

        foreach ($pantries as $code => $data) {
            $aircraftType->settings()->updateOrCreate(
                ['key' => "pantry_{$code}_name", 'airline_id' => $airline->id],
                ['value' => $data['name'], 'type' => 'string', 'description' => 'Name for '.$data['name']]
            );

            $aircraftType->settings()->updateOrCreate(
                ['key' => "pantry_{$code}_weight", 'airline_id' => $airline->id],
                ['value' => $data['weight'], 'type' => 'integer', 'description' => 'Weight for '.$data['name']]
            );

            $aircraftType->settings()->updateOrCreate(
                ['key' => "pantry_{$code}_index", 'airline_id' => $airline->id],
                ['value' => $data['index'], 'type' => 'float', 'description' => 'Index for '.$data['name']]
            );
        }

        $zones = [
            ['name' => 'A', 'max_capacity' => 54, 'arm' => -6.971, 'index' => -0.00697],
            ['name' => 'B', 'max_capacity' => 60, 'arm' => +0.281, 'index' => +0.00028],
            ['name' => 'C', 'max_capacity' => 66, 'arm' => +8.271, 'index' => +0.00827],
        ];

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
            $rows = ceil($zone->max_capacity / 10);
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

            $seats = [];
            for ($row = 1; $row <= $rows; $row++) {
                $actualRow = $lastRowNumber + $row;
                foreach ($columns as $column) {
                    $seats[] = [
                        'aircraft_type_id' => $aircraftType->id,

                        'cabin_zone_id' => $zone->id,
                        'row' => $actualRow,
                        'column' => $column,
                        'designation' => $actualRow.$column,
                        'type' => 'economy',
                        'is_exit' => in_array($actualRow, [12, 13]),
                        'is_blocked' => in_array($actualRow, [1, 10]),
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
                    })->where('is_blocked', false)->get()->pluck('id')->toArray();

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
                'index' => -0.00642,
                'positions' => [
                    ['code' => '11L', 'row' => 11, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '11R', 'row' => 11, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '12L', 'row' => 12, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '12R', 'row' => 12, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '13L', 'row' => 13, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '13R', 'row' => 13, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '14L', 'row' => 14, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '14R', 'row' => 14, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                ],
            ],
            [
                'name' => 'Aft Hold',
                'code' => 'AH',
                'position' => 2,
                'max_weight' => 2426,
                'index' => +0.00401,
                'positions' => [
                    ['code' => '41L', 'row' => 41, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '41R', 'row' => 41, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '42L', 'row' => 42, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '42R', 'row' => 42, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '43L', 'row' => 43, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '43R', 'row' => 43, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '44L', 'row' => 44, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '44R', 'row' => 44, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                ],
            ],
            [
                'name' => 'Bulk Hold',
                'code' => 'BH',
                'position' => 3,
                'max_weight' => 1497,
                'index' => +0.01048,
                'positions' => [
                    ['code' => '51', 'row' => 51, 'side' => null, 'max_weight' => 1134, 'index' => +0.01133],
                    ['code' => '52', 'row' => 52, 'side' => null, 'max_weight' => 1134, 'index' => +0.01133],
                    ['code' => '53', 'row' => 53, 'side' => null, 'max_weight' => 1134, 'index' => +0.01133],
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
    }
}
