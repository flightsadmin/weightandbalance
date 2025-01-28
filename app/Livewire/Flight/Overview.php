<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\WeightBalance;
use Livewire\Component;

class Overview extends Component
{
    public Flight $flight;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load(['airline', 'aircraft', 'crew', 'passengers', 'baggage', 'cargo']);
    }

    public function render()
    {
        return view('livewire.flight.overview')->layout('components.layouts.app');
    }

    public function updateStatus($status)
    {
        $this->flight->status = $status;
        $this->flight->save();
    }

    public function calculateWeightBalance(Flight $flight)
    {
        $passengerWeight = $flight->getTotalPassengerWeight() ?? 0;
        $baggageWeight = $flight->getTotalBaggageWeight() ?? 0;
        $cargoWeight = $flight->getTotalCargoWeight() ?? 0;
        $crewWeight = $flight->getTotalCrewWeight() ?? 0;
        $fuelWeight = $flight->fuel->total_fuel ?? 0;

        // Calculate weights
        $zeroFuelWeight = $passengerWeight + $baggageWeight + $cargoWeight + $crewWeight;
        $takeoffWeight = $zeroFuelWeight + $fuelWeight;
        $landingWeight = $takeoffWeight - ($fuelWeight * 0.85);
        $centerOfGravity = 25.0;

        // Check if weights are within aircraft limits
        $withinLimits =
            $zeroFuelWeight <= $flight->aircraft->type->max_zero_fuel_weight &&
            $takeoffWeight <= $flight->aircraft->type->max_takeoff_weight &&
            $landingWeight <= $flight->aircraft->type->max_landing_weight;

        // Create or update weight balance record
        WeightBalance::updateOrCreate(
            ['flight_id' => $flight->id],
            [
                'zero_fuel_weight' => $zeroFuelWeight,
                'takeoff_fuel_weight' => $fuelWeight,
                'takeoff_weight' => $takeoffWeight,
                'landing_fuel_weight' => $fuelWeight * 0.85,
                'landing_weight' => $landingWeight,
                'passenger_weight_total' => $passengerWeight,
                'baggage_weight_total' => $baggageWeight,
                'cargo_weight_total' => $cargoWeight,
                'crew_weight_total' => $crewWeight,
                'center_of_gravity' => $centerOfGravity,
                'within_limits' => $withinLimits,
                'notes' => $withinLimits ? 'All weights within limits' : 'Weight limits exceeded'
            ]
        );
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Weight calculations updated successfully.'
        ]);
        return $this->redirect(route('flights.show', $flight), true);
    }
}
