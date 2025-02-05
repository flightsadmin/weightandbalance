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
            'weightBalance',
        ]);

        $this->loadplan = $this->flight->loadplans()->latest()->first();
        $this->loadsheet = $this->flight->loadsheets()->latest()->first();
    }

    public function finalizeLoadsheet()
    {
        if (!$this->loadsheet) {
            return;
        }

        $this->loadsheet->update([
            'final' => true,
            'released_by' => auth()->id(),
            'released_at' => now(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet finalized successfully.');
    }

    public function generateLoadsheet()
    {
        // Validate requirements
        if (!$this->flight->fuel) {
            $this->dispatch('alert', icon: 'error', message: 'Fuel data is required to generate loadsheet.');
            return;
        }

        if (!$this->loadplan || $this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Released loadplan is required to generate loadsheet.');
            return;
        }

        // Calculate payload distribution
        $payloadDistribution = $this->getPayloadDistribution();

        // Create new loadsheet
        $this->loadsheet = $this->flight->loadsheets()->create([
            'payload_distribution' => $payloadDistribution,
            'edition' => $this->flight->loadsheets()->count() + 1,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
    }

    private function getPayloadDistribution()
    {
        if (!$this->flight->fuel) {
            return null;
        }

        $pantryDetails = $this->getPantryDistribution();
        $crewDistribution = $this->getCrewDistribution();
        $fuelData = $this->getFuelData();
        $weightAndBalance = $this->getWeightAndBalance();

        // Calculate total weights
        $totalPayload = 0;
        $totalFuel = $fuelData['block'] ?? 0;

        // Add crew weights
        foreach ($crewDistribution as $position => $count) {
            $weight = match ($position) {
                'captain', 'first_officer' => $count * $this->flight->airline->getStandardCockpitCrewWeight(),
                'cabin_crew' => $count * $this->flight->airline->getStandardCabinCrewWeight(),
                default => 0,
            };
            $totalPayload += $weight;
        }

        // Add pantry weight
        if ($pantryDetails) {
            $totalPayload += $pantryDetails['weight'];
        }

        // Add passenger and baggage weights
        $passengerWeight = $this->getTotalPassengerWeight();
        $baggageWeight = $this->flight->baggage->sum('weight');
        $cargoWeight = $this->flight->cargo->sum('weight');
        
        $totalPayload += $passengerWeight + $baggageWeight + $cargoWeight;

        return [
            'weights' => [
                'basic' => $this->flight->aircraft->basic_weight,
                'zero_fuel' => $this->calculateZeroFuelWeight(),
                'takeoff' => $this->calculateTakeoffWeight(),
                'landing' => $this->calculateLandingWeight(),
                'total_payload' => $totalPayload,
                'total_fuel' => $totalFuel,
                'total' => $totalPayload + $totalFuel,
            ],
            'loads' => [
                'passengers' => [
                    'count' => $this->flight->passengers->count(),
                    'weight' => $passengerWeight,
                ],
                'baggage' => [
                    'count' => $this->flight->baggage->count(),
                    'weight' => $baggageWeight,
                ],
                'cargo' => [
                    'count' => $this->flight->cargo->count(),
                    'weight' => $cargoWeight,
                ],
                'crew' => $crewDistribution,
                'pantry' => $pantryDetails,
                'fuel' => $fuelData,
            ],
        ];
    }

    private function getWeightAndBalance()
    {
        return [
            'basic_weight' => $this->flight->aircraft->basic_weight,
            'basic_index' => $this->flight->aircraft->basic_index,
            'zero_fuel_weight' => $this->calculateZeroFuelWeight(),
            'takeoff_weight' => $this->calculateTakeoffWeight(),
            'landing_weight' => $this->calculateLandingWeight(),
        ];
    }

    private function calculateZeroFuelWeight()
    {
        return $this->flight->aircraft->basic_weight
            + $this->getTotalPassengerWeight()
            + $this->getTotalCargoWeight()
            + $this->getTotalCrewWeight()
            + $this->getTotalPantryWeight();
    }

    private function calculateTakeoffWeight()
    {
        return $this->calculateZeroFuelWeight() + $this->flight->fuel->take_off_fuel;
    }

    private function calculateLandingWeight()
    {
        return $this->calculateTakeoffWeight() - $this->flight->fuel->trip_fuel;
    }

    private function getTotalPassengerWeight()
    {
        $standardWeight = $this->flight->airline->getStandardPassengerWeight();
        return $this->flight->passengers->count() * $standardWeight;
    }

    private function getTotalCargoWeight()
    {
        return $this->flight->containers->sum('weight');
    }

    private function getTotalCrewWeight()
    {
        $cockpitCrewWeight = $this->flight->airline->getStandardCockpitCrewWeight();
        $cabinCrewWeight = $this->flight->airline->getStandardCabinCrewWeight();

        return $this->flight->crew->sum(function ($crew) use ($cockpitCrewWeight, $cabinCrewWeight) {
            return $crew->position === 'cabin_crew' ? $cabinCrewWeight : $cockpitCrewWeight;
        });
    }

    private function getTotalPantryWeight()
    {
        $pantryCode = $this->flight->fuel->pantry ?? null;
        if (!$pantryCode) {
            return 0;
        }

        $pantryDetails = $this->flight->aircraft->type->getPantryDetails($pantryCode);
        return $pantryDetails['weight'] ?? 0;
    }

    private function getHoldDistribution()
    {
        $loadsByHold = [];

        foreach ($this->flight->aircraft->type->holds as $hold) {
            $loadsByHold[$hold->id] = [
                'name' => $hold->name,
                'code' => $hold->code,
                'max_weight' => $hold->max_weight,
                'total_weight' => 0,
                'containers' => [],
            ];
        }

        foreach ($this->flight->containers as $container) {
            $holdId = $container->hold_id;
            $loadsByHold[$holdId]['containers'][] = [
                'number' => $container->container_number,
                'weight' => $container->weight,
                'type' => $container->type,
            ];
            $loadsByHold[$holdId]['total_weight'] += $container->weight;
        }

        return $loadsByHold;
    }

    private function getPassengerDistribution()
    {
        return $this->flight->passengers
            ->groupBy('type')
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    private function getCargoDistribution()
    {
        return $this->flight->containers
            ->pluck('cargo')
            ->flatten()
            ->groupBy('type')
            ->map(fn($group) => [
                'pieces' => $group->sum('pieces'),
                'weight' => $group->sum('weight'),
            ])
            ->toArray();
    }

    private function getFuelData()
    {
        if (!$this->flight->fuel) {
            return null;
        }

        return [
            'block' => $this->flight->fuel->block_fuel,
            'taxi' => $this->flight->fuel->taxi_fuel,
            'trip' => $this->flight->fuel->trip_fuel,
            'takeoff' => $this->flight->fuel->take_off_fuel,
        ];
    }

    private function getCrewDistribution()
    {
        return $this->flight->crew
            ->groupBy('position')
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    private function getPantryDistribution()
    {
        $pantryCode = $this->flight->fuel->pantry ?? null;
        if (!$pantryCode) {
            return null;
        }

        $pantryDetails = $this->flight->aircraft->type->getPantryDetails($pantryCode);
        return [
            'code' => $pantryCode,
            'name' => $pantryDetails['name'],
            'weight' => $pantryDetails['weight'],
            'index' => $pantryDetails['index'],
        ];
    }

    public function render()
    {
        return view('livewire.flight.loadsheet-manager', [
            'loadsheets' => $this->flight->loadsheets()->latest()->get(),
        ]);
    }
}
