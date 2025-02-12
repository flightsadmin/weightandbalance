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
        ]);

        $this->loadsheet = $this->flight->loadsheets()->latest()->first();
        $this->loadplan = $this->flight->loadplans()->latest()->first();
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
        $distribution = [
            'flight' => $this->calculateIndices()['flight'],
            'load_data' => $this->generateLoadData(),
            'fuel' => [
                'block' => $this->flight->fuel->block_fuel,
                'taxi' => $this->flight->fuel->taxi_fuel,
                'trip' => $this->flight->fuel->trip_fuel,
                'takeoff' => $this->flight->fuel->take_off_fuel,
                'crew' => $this->flight->fuel->crew,
            ],
            'weights' => [
                'dry_operating_weight' => $this->calculateDryOperatingWeight(),
                'zero_fuel' => $this->calculateZeroFuelWeight(),
                'takeoff' => $this->calculateTakeoffWeight(),
                'landing' => $this->calculateLandingWeight(),
            ],
            'indices' => $this->calculateIndices()['indices'],
        ];

        $this->loadsheet = $this->flight->loadsheets()->create([
            'payload_distribution' => $distribution,
            'edition' => $this->flight->loadsheets()->count() + 1,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
    }

    private function calculateCrewWeight()
    {
        return $this->flight->aircraft->type->getCrewIndexes($this->flight->fuel->crew)['weight'];
    }

    private function calculateDryOperatingWeight()
    {
        return $this->flight->aircraft->basic_weight + $this->calculateCrewWeight();
    }

    private function calculateZeroFuelWeight()
    {
        return $this->flight->aircraft->basic_weight +
            $this->flight->passengers->sum('weight') +
            $this->flight->baggage->sum('weight') +
            $this->flight->cargo->sum('weight');
    }

    private function calculateTakeoffWeight()
    {
        return $this->calculateZeroFuelWeight() + $this->flight->fuel->take_off_fuel;
    }

    private function calculateLandingWeight()
    {
        return $this->calculateTakeoffWeight() - $this->flight->fuel->trip_fuel;
    }

    private function calculateIndices()
    {
        $aircraft = $this->flight->aircraft;
        $type = $aircraft->type;
        $fuel = $this->flight->fuel;
        $crewIndexes = $type->getCrewIndexes($this->flight->fuel->crew);

        $fuelIndexes = $type->getFuelIndexes(
            $fuel->take_off_fuel,
            $fuel->take_off_fuel - $fuel->trip_fuel
        );
        $flight = [
            'number' => $this->flight->flight_number,
            'date' => strtoupper($this->flight->scheduled_departure_time?->format('dMY')),
            'registration' => $aircraft->registration_number,
        ];

        $indices = [
            'pantry' => $this->calculatePantryIndex(),
            'basic_index' => number_format($aircraft->basic_index, 2),
            'crew_index' => number_format($crewIndexes['index'], 2),
            'pax_index' => number_format(array_sum(array_column($this->generateLoadData()['pax_by_type'], 'index')), 2),
            'cargo_index' => number_format(array_sum(array_column($this->generateLoadData()['hold_breakdown'], 'index')), 2),
            'pantry_index' => $this->calculatePantryIndex()['index'] ?? 0,
            'litof' => $fuelIndexes['takeoff'],
            'lildf' => $fuelIndexes['landing'],
        ];
        $indices['doi'] = number_format($indices['basic_index'] + $indices['pantry_index'] + $indices['crew_index'], 2);
        $indices['dli'] = number_format($indices['doi'] + $indices['cargo_index'], 2);
        $indices['lizfw'] = number_format($indices['dli'] + $indices['pax_index'], 2);
        $indices['litow'] = number_format($indices['lizfw'] + $fuelIndexes['takeoff'], 2);
        $indices['lildw'] = number_format($indices['litow'] + $fuelIndexes['landing'], 2);
        $indices['maczfw'] = number_format($type->getZfwMac($this->calculateZeroFuelWeight(), $indices['lizfw']), 2);
        $indices['mactow'] = number_format($type->getTowMac($this->calculateTakeoffWeight(), $indices['litow']), 2);
        $indices['macldw'] = number_format($type->getLdwMac($this->calculateLandingWeight(), $indices['lildw']), 2);

        return ['flight' => $flight, 'indices' => $indices];
    }

    private function calculatePantryIndex()
    {
        if (!$this->flight->fuel) {
            return;
        }

        return $this->flight->aircraft->type->getPantryDetails($this->flight->fuel->pantry);
    }

    private function generateLoadData()
    {
        $passengerTypes = ['male', 'female', 'child', 'infant'];

        $paxByType = $this->flight->passengers->groupBy('type')
            ->map(fn($group) => $group->count());

        $orderedPaxByType = collect($passengerTypes)->mapWithKeys(function ($type) {
            $zones = $this->flight->aircraft->type->cabinZones()
                ->with([
                    'seats.passenger' => function ($query) use ($type) {
                        $query->where('flight_id', $this->flight->id)
                            ->where('type', $type);
                    }
                ])->get();

            $zoneData = $zones->map(function ($zone) use ($type) {
                $passengerCount = $zone->seats->filter(fn($seat) => $seat->passenger)->count();
                $weight = $passengerCount * $this->flight->airline->getStandardPassengerWeight($type);

                return [
                    'zone' => $zone->name,
                    'count' => $passengerCount,
                    'weight' => $weight,
                    'index' => round($weight * $zone->index, 2),
                ];
            })->filter(fn($zone) => $zone['count'] > 0)->values();

            $totalCount = $zoneData->sum('count');
            $totalWeight = $zoneData->sum('weight');
            $totalIndex = $zoneData->sum('index');

            return [
                $type => [
                    'count' => $totalCount,
                    'weight' => $totalWeight,
                    'index' => round($totalIndex, 2),
                    'zones' => $zoneData,
                ]
            ];
        })->toArray();

        $orderedWeightsUsed = collect($passengerTypes)->mapWithKeys(fn($type) => [
            $type => $this->flight->airline->getStandardPassengerWeight($type),
        ])->toArray();

        return [
            'pax_by_type' => $orderedPaxByType,
            'passenger_weights_used' => $orderedWeightsUsed,
            'hold_breakdown' => $this->flight->aircraft->type->holds()->with('positions')->get()->map(function ($hold) {
                $weight = $this->flight->containers->whereIn('position_id', $hold->positions->pluck('id'))->sum('weight');
                return [
                    'hold_no' => $hold->code,
                    'weight' => $weight,
                    'index' => round($weight * $hold->index, 2),
                ];
            })->filter(fn($hold) => $hold['weight'] > 0)->values()->toArray(),
            'deadload_by_type' => [
                'C' => [
                    'pieces' => $this->flight->cargo->sum('pieces'),
                    'weight' => $this->flight->cargo->sum('weight'),
                ],
                'B' => [
                    'pieces' => $this->flight->baggage->count(),
                    'weight' => $this->flight->baggage->sum('weight'),
                ],
                'M' => [
                    'pieces' => 0,
                    'weight' => 0,
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.flights.loadsheet-manager', [
            'loadsheets' => $this->flight->loadsheets()->latest()->get(),
        ]);
    }
}
