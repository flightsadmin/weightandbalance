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
            'load_data' => $this->generateLoadData(),
            'fuel' => [
                'block' => $this->flight->fuel->block_fuel,
                'taxi' => $this->flight->fuel->taxi_fuel,
                'trip' => $this->flight->fuel->trip_fuel,
                'takeoff' => $this->flight->fuel->take_off_fuel,
            ],
            'crew' => [
                'flight_deck' => $this->flight->crew->whereIn('position', ['captain', 'first_officer'])->count(),
                'cabin' => $this->flight->crew->whereIn('position', ['cabin_crew', 'purser'])->count(),
            ],
            'weights' => [
                'dry_operating_weight' => $this->calculateDryOperatingWeight(),
                'zero_fuel' => $this->calculateZeroFuelWeight(),
                'takeoff' => $this->calculateTakeoffWeight(),
                'landing' => $this->calculateLandingWeight(),
            ],
            'indices' => $this->calculateIndices(),
        ];

        $this->loadsheet = $this->flight->loadsheets()->create([
            'payload_distribution' => $distribution,
            'edition' => $this->flight->loadsheets()->count() + 1,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
    }

    private function calculateDryOperatingWeight()
    {
        return $this->flight->aircraft->basic_weight;
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
        $indices = [
            'passengers' => $this->calculatePassengerIndices(),
            'pantry' => $this->calculatePantryIndex(),
            'basic_index' => number_format($this->flight->aircraft->basic_index, 2),
            'pax_index' => number_format(array_sum(array_column($this->calculatePassengerIndices(), 'index')), 2),
            'cargo_index' => number_format(array_sum(array_column($this->generateLoadData()['hold_breakdown'], 'index')), 2),
            'pantry_index' => $this->calculatePantryIndex()['index'] ?? 0,
        ];
        $indices['doi'] = number_format($indices['basic_index'] + $indices['pantry_index'], 2); //TODO: Add crew index
        $indices['dli'] = number_format($indices['doi'] + $indices['cargo_index'], 2);
        $indices['lizfw'] = number_format($indices['dli'] + $indices['pax_index'], 2);
        $indices['litow'] = number_format($indices['lizfw'], 2); //TODO: Add takeoff_fuel index
        $indices['lildw'] = number_format($indices['litow'], 2); //TODO: Add landing_fuel index
        $indices['maczfw'] = number_format($indices['lizfw'] + $indices['pantry_index'], 2); //TODO: correct this
        $indices['mactow'] = number_format($indices['litow'] + $indices['pantry_index'], 2); //TODO: correct this

        return $indices;
    }

    private function calculatePassengerIndices()
    {
        return $this->flight->aircraft->type->cabinZones()
            ->with([
                'seats.passenger' => function ($query) {
                    $query->where('flight_id', $this->flight->id);
                },
            ])
            ->get()
            ->map(function ($zone) {
                $passengerCount = $zone->seats
                    ->filter(fn($seat) => $seat->passenger)
                    ->count();
                $weight = $passengerCount * $this->flight->airline->getStandardPassengerWeight('male');

                return [
                    'zone_name' => $zone->name,
                    'passenger_count' => $passengerCount,
                    'weight' => $weight,
                    'index' => round($weight * $zone->index, 2),
                ];
            })->filter(fn($zone) => $zone['passenger_count'] > 0)->toArray();
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
        $paxByType = $this->flight->passengers->groupBy('type')->map(fn($group) => $group->count());

        $orderedPaxByType = collect($passengerTypes)->mapWithKeys(fn($type) => [
            $type => $paxByType[$type] ?? 0,
        ])->toArray();

        $orderedPaxByTypes = collect($passengerTypes)->mapWithKeys(fn($type) => [
            $type => [
                'count' => $paxByType[$type] ?? 0,
                'weight' => $paxByType[$type] * $this->flight->airline->getStandardPassengerWeight($type),
                'index' => $paxByType[$type] * $this->flight->airline->getStandardPassengerWeight($type) * $this->flight->aircraft->type->cabinZones->first()->index,
            ],
        ])->toArray();

        $orderedWeights = collect($passengerTypes)->mapWithKeys(fn($type) => [
            $type => $paxByType[$type] * $this->flight->airline->getStandardPassengerWeight($type),
        ])->toArray();

        $orderedWeightsUsed = collect($passengerTypes)->mapWithKeys(fn($type) => [
            $type => $this->flight->airline->getStandardPassengerWeight($type),
        ])->toArray();

        return [
            'pax_by_type' => $orderedPaxByType,
            'pax_by_types' => $orderedPaxByTypes,
            'passenger_weights' => $orderedWeights,
            'passenger_weights_used' => $orderedWeightsUsed,
            'hold_breakdown' => $this->flight->aircraft->type->holds()->with('positions')->get()->map(function ($hold) {
                return [
                    'hold_no' => $hold->code,
                    'weight' => $this->flight->containers->whereIn('position_id', $hold->positions->pluck('id'))->sum('weight'),
                    'index' => number_format($this->flight->containers->whereIn('position_id', $hold->positions->pluck('id'))->sum('weight') * $hold->index, 2),
                ];
            })->filter(fn($hold) => $hold['weight'] > 0)->values()->toArray(),
            'passenger_breakdown' => $this->flight->passengers->groupBy('seat.cabin_zone_id')->map(fn($group) => $group->count())->toArray(),

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

    protected function calculatePassengerWeights()
    {
        $airline = $this->flight->airline;
        $passengers = $this->flight->passengers;

        $weights = [
            'male' => $airline->getStandardPassengerWeight('male'),
            'female' => $airline->getStandardPassengerWeight('female'),
            'child' => $airline->getStandardPassengerWeight('child'),
            'infant' => $airline->getStandardPassengerWeight('infant'),
        ];

        $orderedWeights = [];

        foreach ($this->flight->aircraft->type->cabinZones as $zone) {
            $zonePassengers = $passengers->where('seat.cabin_zone_id', $zone->id);

            $orderedWeights[$zone->name] = [
                'male' => $zonePassengers->where('type', 'male')->count() * $weights['male'],
                'female' => $zonePassengers->where('type', 'female')->count() * $weights['female'],
                'child' => $zonePassengers->where('type', 'child')->count() * $weights['child'],
                'infant' => $zonePassengers->where('type', 'infant')->count() * $weights['infant'],
            ];
        }

        return [$orderedWeights, $weights];
    }

    public function render()
    {
        return view('livewire.flight.loadsheet-manager', [
            'loadsheets' => $this->flight->loadsheets()->latest()->get(),
        ]);
    }
}
