<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\Setting;
use Livewire\Component;

class Summary extends Component
{
    public Flight $flight;
    public $showModal = false;
    public $summary = [];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function generateSummary()
    {
        $airlineId = session('selected_airline_id');
        $settings = Setting::where('airline_id', $airlineId)
            ->whereIn('key', [
                'standard_passenger_weight',
                'standard_crew_weight',
                'standard_baggage_weight',
                'standard_cargo_density',
                'standard_fuel_density'
            ])->get()->pluck('value', 'key');

        // 1. Passenger Summary
        $passengers = $this->flight->passengers->where('acceptance_status', 'accepted');
        $passengersByGender = $passengers->groupBy('type')->map->count();

        // Group passengers by zone based on seat numbers
        $passengersByZone = $passengers->groupBy(function ($passenger) {
            // Extract seat number from seat assignment
            preg_match('/\d+/', $passenger->seat_number, $matches);
            $seatNumber = $matches[0] ?? 0;

            // Calculate zone (1-10 = zone 1, 11-20 = zone 2, etc.)
            return 'Zone ' . str_pad(ceil($seatNumber / 10), 3, '0', STR_PAD_LEFT);
        })->map->count();

        $totalPassengerWeight = $passengers->count() * ($settings['standard_passenger_weight'] ?? 75);
        // 2. Crew Summary
        $crews = $this->flight->crews;
        $crewWeight = $crews->count() * ($settings['standard_crew_weight'] ?? 75);

        // 3. Baggage Summary by Hold
        $baggageByHold = $this->flight->containers()
            ->where('type', 'baggage')
            ->where('status', 'loaded')
            ->get()
            ->groupBy('compartment')
            ->map(function ($containers) use ($settings) {
                return [
                    'count' => $containers->count(),
                    'weight' => $containers->sum('weight')
                ];
            });

        // 4. Cargo Summary by Hold
        $cargoByHold = $this->flight->containers()
            ->where('type', 'cargo')
            ->where('status', 'loaded')
            ->get()
            ->groupBy('compartment')
            ->map(function ($containers) use ($settings) {
                return [
                    'count' => $containers->count(),
                    'weight' => $containers->sum('weight')
                ];
            });

        // 5. Fuel Summary
        $fuelDensity = $settings['standard_fuel_density'] ?? 0.8;
        $fuelWeight = $this->flight->fuel->total_fuel * $fuelDensity;

        $this->summary = [
            'passengers' => [
                'by_gender' => $passengersByGender,
                'by_zone' => $passengersByZone,
                'total_weight' => $totalPassengerWeight,
                'count' => $passengers->count(),
            ],
            'crew' => [
                'count' => $crews->count(),
                'weight' => $crewWeight,
            ],
            'baggage' => $baggageByHold,
            'cargo' => $cargoByHold,
            'fuel' => [
                'quantity' => $this->flight->fuel->total_fuel,
                'density' => $fuelDensity,
                'weight' => $fuelWeight,
            ],
            'total_weight' => $totalPassengerWeight + $crewWeight +
                $baggageByHold->sum('weight') +
                $cargoByHold->sum('weight') +
                $fuelWeight,
        ];

        $this->showModal = true;
        $this->dispatch('show-summary');
    }

    public function render()
    {
        return view('livewire.flight.summary');
    }
}