<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\Loadsheet;
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
            'weightBalance',
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
            'passengers' => $this->getPassengerDistribution(),
            'cargo' => $this->getCargoDistribution(),
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
                'zero_fuel' => $this->calculateZeroFuelIndex(),
                'takeoff' => $this->calculateTakeoffIndex(),
                'landing' => $this->calculateLandingIndex(),
            ],
            'pantry' => $this->flight->fuel->pantry,
        ];

        // Create new loadsheet
        $this->loadsheet = $this->flight->loadsheets()->create([
            'payload_distribution' => $distribution,
            'edition' => $this->flight->loadsheets()->count() + 1,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
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
        // Implement index calculation logic based on aircraft type settings
        return 0;
    }

    private function calculateTakeoffIndex()
    {
        // Implement takeoff index calculation
        return 0;
    }

    private function calculateLandingIndex()
    {
        // Implement landing index calculation
        return 0;
    }

    private function getCargoDistribution()
    {
        return [
            'total_weight' => $this->flight->cargo->sum('weight'),
            'total_pieces' => $this->flight->cargo->sum('pieces'),
            'by_type' => $this->flight->cargo
                ->groupBy('type')
                ->map(fn($group) => [
                    'weight' => $group->sum('weight'),
                    'pieces' => $group->sum('pieces'),
                ])
                ->toArray(),
        ];
    }

    private function getLoadsByHold()
    {
        $loadsByHold = [];
        foreach ($this->flight->aircraft->type->holds as $hold) {
            $loadsByHold[$hold->id] = [
                'name' => $hold->name,
                'code' => $hold->code,
                'total_weight' => 0,
                'max_weight' => $hold->max_weight,
            ];
        }

        foreach ($this->flight->containers as $container) {
            $holdId = $container->hold_id;
            $loadsByHold[$holdId]['total_weight'] += $container->cargo->sum('weight');
        }

        dd($loadsByHold);

        return $loadsByHold;
    }

    private function getPassengerDistribution()
    {
        return $this->flight->passengers
            ->groupBy('type')
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    public function render()
    {
        return view('livewire.flight.loadsheet-manager', [
            'loadsheets' => $this->flight->loadsheets()->latest()->get(),
        ]);
    }
}
