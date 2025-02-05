<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\AircraftType;
use App\Models\Airline;
use App\Models\Baggage;
use App\Models\Cargo;
use App\Models\Container;
use App\Models\Crew;
use App\Models\Flight;
use App\Models\Fuel;
use App\Models\Hold;
use App\Models\HoldPosition;
use App\Models\Passenger;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        Airline::factory(2)->create()->each(function ($airline) {
            AircraftType::factory(1)->forAirline($airline)->create()->each(function ($aircraftType) use ($airline) {
                Aircraft::factory(3)->create([
                    'airline_id' => $airline->id,
                    'aircraft_type_id' => $aircraftType->id,
                ])->each(function ($aircraft) use ($airline, $aircraftType) {
                    Hold::factory()->forAircraftType($aircraftType)->create()->each(function ($hold) {
                        $hold->positions()->saveMany(HoldPosition::factory(2)->make());
                    });

                    Flight::factory(rand(1, 3))->forAirline($airline)->create([
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

                        Passenger::factory(rand(10, 30))->forFlight($flight)->create()->each(function ($passenger) use ($flight) {
                            $passenger->baggage()->saveMany(Baggage::factory(rand(1, 2))->make([
                                'flight_id' => $flight->id,
                            ]));
                        });

                        Cargo::factory(rand(5, 10))->create([
                            'flight_id' => $flight->id,
                        ]);

                        Fuel::factory()->create([
                            'flight_id' => $flight->id,
                        ]);

                        Container::factory(rand(1, 3))->forFlight($flight)->create();
                    });
                });
                $settings = [
                    [
                        'airline_id' => $airline->id,
                        'key' => Airline::STANDARD_COCKPIT_CREW_WEIGHT,
                        'value' => 85,
                    ],
                    [
                        'airline_id' => $airline->id,
                        'key' => Airline::STANDARD_CABIN_CREW_WEIGHT,
                        'value' => 75,
                    ],
                    [
                        'airline_id' => $airline->id,
                        'key' => 'standard_crew_weight',
                        'value' => 85,
                    ],
                    [
                        'airline_id' => $airline->id,
                        'key' => 'standard_baggage_weight',
                        'value' => 20,
                    ],
                    [
                        'airline_id' => $airline->id,
                        'key' => Airline::STANDARD_PASSENGER_WEIGHT,
                        'value' => 84,
                    ],
                    [
                        'airline_id' => $airline->id,
                        'key' => Airline::STANDARD_PANTRY_WEIGHT,
                        'value' => 250,
                    ],
                ];
                Setting::insert($settings);
            });
        });
    }
}
