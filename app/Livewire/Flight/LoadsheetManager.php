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
        // Calculate payload distribution
        $distribution = [
            'passengers' => $this->getPassengerWeightDistribution(),
            'baggage' => $this->getBaggageDistribution(),
            'cargo' => $this->getCargoDistribution(),
            'total_traffic_load' => $this->calculateTotalTrafficLoad(),
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
            'holds' => $this->getLoadsByHold(),
            'weights' => [
                'zero_fuel' => $this->calculateZeroFuelWeight(),
                'takeoff' => $this->calculateTakeoffWeight(),
                'landing' => $this->calculateLandingWeight(),
            ],
            'indices' => [
                'dry_operating_weight' => $this->calculateDryOperatingWeight(),
                'zero_fuel' => $this->calculateZeroFuelIndex(),
                'takeoff' => $this->calculateTakeoffIndex(),
                'landing' => $this->calculateLandingIndex(),
            ],
            'pantry' => $this->flight->fuel->pantry,
            'ldm' => $this->generateLdmData(),
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

    private function calculateTotalTrafficLoad()
    {
        return $this->flight->passengers->sum('weight') +
            $this->flight->baggage->sum('weight') +
            $this->flight->cargo->sum('weight');
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

    private function calculateZeroFuelIndex()
    {
        return 0;
    }

    private function calculateTakeoffIndex()
    {
        return 0;
    }

    private function calculateLandingIndex()
    {
        return 0;
    }

    private function getCargoDistribution()
    {
        return [
            'total_pieces' => $this->flight->cargo->sum('pieces'),
            'total_weight' => $this->flight->cargo->sum('weight'),
        ];
    }

    private function getBaggageDistribution()
    {
        return [
            'total_pieces' => $this->flight->baggage->count(),
            'total_weight' => $this->flight->baggage->sum('weight'),
        ];
    }

    private function getLoadsByHold()
    {
        $loadsByHold = [];
        foreach ($this->flight->aircraft->type->holds as $hold) {
            $totalWeight = $this->flight->containers
                ->whereIn('position_id', $hold->positions->pluck('id'))
                ->sum('weight');

            $loadsByHold[$hold->code] = [
                'name' => $hold->name,
                'code' => $hold->code,
                'total_weight' => $totalWeight,
                'max_weight' => $hold->max_weight,
            ];
        }
        return $loadsByHold;
    }

    private function getPassengerWeightDistribution()
    {
        return $this->flight->passengers
            ->groupBy('type')
            ->map(fn($group) => [
                'count' => $group->count(),
                'weight' => $group->count() * $this->flight->airline->getStandardPassengerWeight(),
            ])
            ->toArray();
    }

    private function generateLdmData()
    {
        $passengerTypes = ['male', 'female', 'child', 'infant'];
        $paxByType = $this->flight->passengers
            ->groupBy('type')
            ->map(fn($group) => $group->count());

        // Ensure passenger types are in correct order
        $orderedPaxByType = collect($passengerTypes)
            ->mapWithKeys(fn($type) => [$type => $paxByType[$type] ?? 0])
            ->toArray();

        $orderedWeights = collect($passengerTypes)
            ->mapWithKeys(fn($type) => [
                $type => $this->flight->airline->getStandardPassengerWeight()
            ])
            ->toArray();

        return [
            'pax_by_type' => $orderedPaxByType,
            'passenger_weights_used' => $orderedWeights,
            'hold_breakdown' => $this->flight->aircraft->type->holds()
                ->with('positions')
                ->get()
                ->map(function ($hold) {
                    return [
                        'hold_no' => $hold->code,
                        'weight' => $this->flight->containers
                            ->whereIn('position_id', $hold->positions->pluck('id'))
                            ->sum('weight')
                    ];
                })
                ->filter(fn($hold) => $hold['weight'] > 0)
                ->values()
                ->toArray(),
            'deadload_by_type' => [
                'C' => $this->flight->cargo->sum('weight'),
                'B' => $this->flight->baggage->sum('weight'),
                'M' => 0,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.flight.loadsheet-manager', [
            'loadsheets' => $this->flight->loadsheets()->latest()->get(),
        ]);
    }
}
