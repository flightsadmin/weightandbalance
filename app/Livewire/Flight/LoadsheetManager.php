<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class LoadsheetManager extends Component
{
    public Flight $flight;

    public $loadsheet;

    public $loadplan;

    public $showModal = false;

    public $pantryCodes = [];

    public $newPantryCode;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load([
            'aircraft.type.holds',
            'containers.cargo',
            'containers.baggage',
            'passengers',
            'crew',
            'fuel',
            'loadplans',
            'weightBalance',
        ]);

        $this->loadplan = $this->flight->loadplans()->latest()->first();
        $this->pantryCodes = $this->flight->pantryCodes()->pluck('code')->toArray();
    }

    public function finalizeLoadsheet()
    {
        $this->loadsheet->update([
            'final' => true,
            'released_by' => auth()->id(),
            'released_at' => now(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet finalized successfully.');
    }

    public function generateLoadsheet()
    {
        if (! $this->flight->fuel) {
            $this->dispatch('alert', icon: 'error', message: 'Fuel data must be added before generating loadsheet.');

            return;
        }

        if (! $this->loadplan || $this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Load plan must be released before generating loadsheet.');

            return;
        }
        // Get airline standard weights
        $airline = $this->flight->airline;
        $standardWeights = [
            'standard_male_weight' => $airline->getStandardPassengerWeight(),
            'standard_female_weight' => $airline->getStandardPassengerWeight(),
            'standard_child_weight' => 35,
            'standard_infant_weight' => 10,
            'standard_captain_weight' => $airline->getStandardCockpitCrewWeight(),
            'standard_first_officer_weight' => $airline->getStandardCockpitCrewWeight(),
            'standard_cabin_crew_weight' => $airline->getStandardCabinCrewWeight(),
        ];

        // Calculate weights
        $weights = [
            'passenger' => $this->calculatePassengerWeight($standardWeights),
            'cargo' => $this->calculateCargoWeight(),
            'baggage' => $this->calculateBaggageWeight(),
            'crew' => $this->calculateCrewWeight($standardWeights),
        ];

        // Get fuel data
        $fuel = [
            'block' => $this->flight->fuel->block_fuel,
            'taxi' => $this->flight->fuel->taxi_fuel,
            'trip' => $this->flight->fuel->trip_fuel,
            'take_off' => $this->flight->fuel->block_fuel - $this->flight->fuel->taxi_fuel,
        ];

        // Calculate operating weights
        $operatingWeights = $this->calculateOperatingWeights($weights, $fuel);

        // Calculate weight and balance
        $balance = $this->calculateBalance($operatingWeights);

        // Get load distribution by compartment
        $loads = $this->calculateCompartmentLoads();

        // Get crew distribution
        $crewDistribution = [
            'deck' => $this->flight->crew->whereIn(
                'position',
                [
                    'captain',
                    'first_officer',
                ]
            )->count(),
            'cabin' => $this->flight->crew->where('position', 'cabin_crew')->count(),
        ];

        $distribution = [
            'weights' => array_merge($weights, $operatingWeights),
            'fuel' => $fuel,
            'loads' => $loads,
            'balance' => $balance,
            'passenger_distribution' => $this->getPassengerDistribution(),
            'crew_distribution' => $crewDistribution,
            'pantry_codes' => $this->pantryCodes,
        ];

        // Create new loadsheet
        $this->loadsheet = $this->flight->loadsheets()->create([
            'payload_distribution' => $distribution,
            'created_by' => auth()->id(),
            'edition' => $this->flight->loadsheets()->count() + 1,
        ]);

        // Create weight balance record if needed
        if (! $this->flight->weightBalance) {
            $this->flight->weightBalance()->create([
                'weights' => $distribution['weights'
                ],
                'balance' => $distribution['balance'
                ],
            ]);
        }

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
    }

    private function calculateOperatingWeights($weights, $fuel)
    {
        $aircraft = $this->flight->aircraft;

        $dow = $aircraft->basic_weight + $weights['crew'
        ];
        $zfw = $dow + $weights['passenger'
        ] + $weights['cargo'
                ] + $weights['baggage'
                ];
        $tow = $zfw + $fuel['block'
        ] - $fuel['taxi'
                ];
        $ldw = $tow - $fuel['trip'
        ];

        return [
            'dry_operating' => $dow,
            'zero_fuel' => $zfw,
            'take_off' => $tow,
            'landing' => $ldw,
        ];
    }

    private function calculateBalance($weights)
    {
        $type = $this->flight->aircraft->type;
        $settings = $type->settings()->pluck('value', 'key');

        // Get MAC parameters
        $refSta = (float) $settings->get(
            'ref_sta',
            18.850
        ); // Reference station at 18.850 meters
        $lemac = (float) $settings->get(
            'lemac_at',
            17.8015
        ); // LEMAC at 17.8015 meters
        $mac = (float) $settings->get(
            'length_of_mac',
            4.194
        ); // MAC length 4.194 meters
        $k = (float) $settings->get(
            'k_constant',
            50
        ); // K constant = 50
        $c = (float) $settings->get(
            'c_constant',
            1000
        ); // C constant = 1000

        // Calculate indices and MAC percentages for each weight condition
        $zfw = $this->calculateWeightBalance($weights['zero_fuel'
        ], $refSta, $lemac, $mac, $k, $c);
        $tow = $this->calculateWeightBalance($weights['take_off'
        ], $refSta, $lemac, $mac, $k, $c);
        $ldw = $this->calculateWeightBalance($weights['landing'
        ], $refSta, $lemac, $mac, $k, $c);

        return [
            'mac_parameters' => [
                'ref_sta' => $refSta,
                'lemac' => $lemac,
                'mac' => $mac,
                'k' => $k,
                'c' => $c,
            ],
            'indices' => [
                'zfw' => $zfw['index'
                ],
                'tow' => $tow['index'
                ],
                'ldw' => $ldw['index'
                ],
            ],
            'mac_percentages' => [
                'zfw' => $zfw['mac_percentage'
                ],
                'tow' => $tow['mac_percentage'
                ],
                'ldw' => $ldw['mac_percentage'
                ],
            ],
            'arms' => [
                'zfw' => $zfw['arm'
                ],
                'tow' => $tow['arm'
                ],
                'ldw' => $ldw['arm'
                ],
            ],
        ];
    }

    private function calculateWeightBalance($weight, $refSta, $lemac, $mac, $k, $c)
    {
        $arm = $this->calculateArm($weight);
        $index = round(
            ($weight * ($arm - $refSta)) / $c + $k,
            1
        );
        $macPercentage = round(
            (($c * ($index - $k)) / $weight + $refSta - $lemac) / ($mac / 100),
            1
        );

        return [
            'arm' => $arm,
            'index' => $index,
            'mac_percentage' => $macPercentage,
        ];
    }

    private function calculateArm($weight)
    {
        $aircraft = $this->flight->aircraft;
        $basicArm = $aircraft->basic_index;
        $weightFactor = 0.01;

        return round(
            $basicArm + ($weight - $aircraft->basic_weight) * $weightFactor,
            3
        );
    }

    private function calculatePassengerWeight($standardWeights)
    {
        return $this->flight->passengers->sum(function ($passenger) use ($standardWeights) {
            return $standardWeights[
                "standard_{$passenger->type}_weight"
            ] ?? 75;
        });
    }

    private function calculateCargoWeight()
    {
        return $this->flight->containers
            ->sum(fn ($container) => $container->cargo->sum('weight'));
    }

    private function calculateBaggageWeight()
    {
        return $this->flight->containers
            ->sum(fn ($container) => $container->baggage->sum('weight'));
    }

    private function calculateCrewWeight($standardWeights)
    {
        return $this->flight->crew->sum(function ($crew) use ($standardWeights) {
            return $standardWeights[
                "standard_{$crew->position}_weight"
            ] ?? 75;
        });
    }

    private function calculateCompartmentLoads()
    {
        $holds = $this->flight->aircraft->type->holds()
            ->with('positions')
            ->get()
            ->keyBy('id');

        $loadsByHold = [];

        foreach ($this->loadplan->container_positions as $containerId => $positionId) {
            $container = $this->flight->containers->find($containerId);
            $position = $holds->first(function ($hold) use ($positionId) {
                return $hold->positions->contains('id', $positionId);
            });

            if (! $container || ! $position) {
                continue;
            }

            $holdId = $position->id;
            if (
                ! isset($loadsByHold[$holdId
                ])
            ) {
                $loadsByHold[$holdId
                ] = [
                    'hold_name' => $position->name,
                    'cargo_weight' => 0,
                    'baggage_weight' => 0,
                    'total_weight' => 0,
                ];
            }

            $loadsByHold[$holdId
            ]['cargo_weight'
            ] += $container->cargo->sum('weight');
            $loadsByHold[$holdId
            ]['baggage_weight'
            ] += $container->baggage->sum('weight');
            $loadsByHold[$holdId
            ]['total_weight'
            ] += $container->total_weight;
        }

        return $loadsByHold;
    }

    private function getPassengerDistribution()
    {
        return $this->flight->passengers
            ->groupBy('type')
            ->map(fn ($group) => $group->count())
            ->toArray();
    }

    public function addPantryCode()
    {
        if ($this->newPantryCode) {
            $this->flight->pantryCodes()->create(['code' => $this->newPantryCode]);
            $this->pantryCodes[] = $this->newPantryCode;
            $this->newPantryCode = '';
            $this->dispatch('alert', icon: 'success', message: 'Pantry code added successfully.');
        }
    }

    public function removePantryCode($code)
    {
        $this->flight->pantryCodes()->where('code', $code)->delete();
        $this->pantryCodes = array_filter($this->pantryCodes, fn ($c) => $c !== $code);
        $this->dispatch('alert', icon: 'success', message: 'Pantry code removed successfully.');
    }

    public function render()
    {
        return view(
            'livewire.flight.loadsheet-manager',
            [
                'loadsheets' => $this->flight->loadsheets()->latest()->get(),
                'pantryCodes' => $this->pantryCodes,
            ]
        );
    }
}
