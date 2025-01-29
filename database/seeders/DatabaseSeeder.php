<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Fuel;
use App\Models\Hold;
use App\Models\Cargo;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Baggage;
use App\Models\Aircraft;
use App\Models\Container;
use App\Models\Passenger;
use App\Models\AircraftType;
use App\Models\HoldPosition;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        Airline::factory(2)->create()->each(function ($airline) {
            AircraftType::factory(1)->create()->each(function ($aircraftType) use ($airline) {
                $airline->aircraftTypes()->sync($aircraftType->id);
                Aircraft::factory(3)->create([
                    'airline_id' => $airline->id,
                    'aircraft_type_id' => $aircraftType->id
                ])->each(function ($aircraft) use ($airline, $aircraftType) {
                    Hold::factory(2)->create([
                        'aircraft_type_id' => $aircraftType->id
                    ])->each(function ($hold) {
                        HoldPosition::factory(4)->create([
                            'hold_id' => $hold->id
                        ]);
                    });

                    Flight::factory(rand(1, 3))->create([
                        'airline_id' => $airline->id,
                        'aircraft_id' => $aircraft->id,
                    ])->each(function ($flight) {
                        $captain = Crew::factory()->captain()->create();
                        $captain->flights()->attach($flight);

                        $firstOfficer = Crew::factory()->firstOfficer()->create();
                        $firstOfficer->flights()->attach($flight);

                        Crew::factory(rand(4, 6))->cabinCrew()->create()
                            ->each(function ($crew) use ($flight) {
                                $crew->flights()->attach($flight);
                            });

                        Passenger::factory(rand(10, 30))->create([
                            'flight_id' => $flight->id
                        ])->each(function ($passenger) use ($flight) {
                            $passenger->baggage()->saveMany(Baggage::factory(rand(1, 2))->make([
                                'flight_id' => $flight->id
                            ]));
                        });

                        Cargo::factory(rand(5, 10))->create([
                            'flight_id' => $flight->id
                        ]);

                        Fuel::factory()->create([
                            'flight_id' => $flight->id
                        ]);

                        Container::factory(rand(1, 3))->create([
                            'flight_id' => $flight->id
                        ]);
                    });
                });
            });
        });
    }
}

