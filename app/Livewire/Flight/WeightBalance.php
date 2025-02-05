<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\WeightBalance as WeightBalanceModel;
use Livewire\Component;

class WeightBalance extends Component
{
    public Flight $flight;

    public $showSummaryModal = false;

    public $summary = [];

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load([
            'aircraft.type',
            'passengers',
            'baggage',
            'cargo',
            'fuel',
            'containers',
            'weightBalance',
        ]);
    }

    protected function calculateWeights()
    {
        $aircraft = $this->flight->aircraft;
        $type = $aircraft->type;
        $airline = $this->flight->airline;

        // Basic aircraft weights
        $weights = [
            'basic_empty_weight' => $type->empty_weight,
            'max_takeoff_weight' => $type->max_takeoff_weight,
            'max_landing_weight' => $type->max_landing_weight,
            'max_zero_fuel_weight' => $type->max_zero_fuel_weight,
        ];
        // Crew weights
        $crew = $this->flight->fuel?->crew ?? '2/4';
        [$cockpitCrew, $cabinCrew] = explode('/', $crew);
        $crewWeight = ($cockpitCrew * $airline->getStandardCockpitCrewWeight()) +
            ($cabinCrew * $airline->getStandardCabinCrewWeight());

        // Pantry weight based on configuration
        $pantryWeight = match ($this->flight->fuel?->pantry ?? 'A') {
            'A' => $airline->getStandardPantryWeight(),
            'B' => $airline->getStandardPantryWeight() + 100,
            'C' => $airline->getStandardPantryWeight() + 200,
            default => $airline->getStandardPantryWeight()
        };

        // Operating weights
        $weights['operating_empty_weight'] = $weights['basic_empty_weight'] + $crewWeight + $pantryWeight;

        // Passenger weights
        $weights['passenger_weight'] = $this->flight->passengers->count() * $airline->getStandardPassengerWeight();

        // Baggage weights
        $weights['baggage_weight'] = $this->flight->baggage->sum('weight');

        // Cargo weights
        $weights['cargo_weight'] = $this->flight->cargo->sum('weight');

        // Container tare weights
        $weights['container_tare_weight'] = $this->flight->containers->sum('tare_weight');

        // Total payload
        $weights['total_payload'] = $weights['passenger_weight'] +
            $weights['baggage_weight'] +
            $weights['cargo_weight'] +
            $weights['container_tare_weight'];

        // Zero Fuel Weight
        $weights['zero_fuel_weight'] = $weights['operating_empty_weight'] + $weights['total_payload'];

        // Fuel weights
        $fuel = $this->flight->fuel;
        $weights['block_fuel'] = $fuel->block_fuel ?? 0;
        $weights['taxi_fuel'] = $fuel->taxi_fuel ?? 0;
        $weights['take_off_fuel'] = $fuel->take_off_fuel ?? 0;
        $weights['trip_fuel'] = $fuel->trip_fuel ?? 0;

        // Take-off weight
        $weights['take_off_weight'] = $weights['zero_fuel_weight'] + $weights['take_off_fuel'];

        // Landing weight
        $weights['landing_weight'] = $weights['take_off_weight'] - $weights['trip_fuel'];

        // Calculate weight limits and differences
        $weights['max_takeoff_weight_diff'] = $weights['max_takeoff_weight'] - $weights['take_off_weight'];
        $weights['max_landing_weight_diff'] = $weights['max_landing_weight'] - $weights['landing_weight'];
        $weights['max_zero_fuel_weight_diff'] = $weights['max_zero_fuel_weight'] - $weights['zero_fuel_weight'];

        // Check if weights are within limits
        $weights['is_takeoff_weight_ok'] = $weights['take_off_weight'] <= $weights['max_takeoff_weight'];
        $weights['is_landing_weight_ok'] = $weights['landing_weight'] <= $weights['max_landing_weight'];
        $weights['is_zero_fuel_weight_ok'] = $weights['zero_fuel_weight'] <= $weights['max_zero_fuel_weight'];

        return $weights;
    }

    public function generateSummary()
    {
        // 1. Passenger Summary
        $passengers = $this->flight->passengers->where('acceptance_status', 'accepted');
        $passengersByGender = $passengers->groupBy('type')->map->count();

        // Group passengers by zone based on seat numbers
        $passengersByZone = $passengers->groupBy(function ($passenger) {
            preg_match('/\d+/', $passenger->seat_number, $matches);
            $seatNumber = $matches[0] ?? 0;

            return 'Zone '.str_pad(ceil($seatNumber / 10), 3, '0', STR_PAD_LEFT);
        })->map->count();

        // 2. Baggage Summary by Hold
        $baggageByHold = $this->flight->containers()
            ->where('type', 'baggage')
            ->where('status', 'loaded')
            ->get()
            ->groupBy('compartment')
            ->map(function ($containers) {
                return [
                    'count' => $containers->count(),
                    'weight' => $containers->sum('weight'),
                ];
            });

        // 3. Cargo Summary by Hold
        $cargoByHold = $this->flight->containers()
            ->where('type', 'cargo')
            ->where('status', 'loaded')
            ->get()
            ->groupBy('compartment')
            ->map(function ($containers) {
                return [
                    'count' => $containers->count(),
                    'weight' => $containers->sum('weight'),
                ];
            });

        $this->summary = [
            'passengers' => [
                'by_gender' => $passengersByGender,
                'by_zone' => $passengersByZone,
                'count' => $passengers->count(),
                'total_weight' => $passengers->count() * ($settings['standard_passenger_weight'] ?? 75),
            ],
            'baggage' => $baggageByHold,
            'cargo' => $cargoByHold,
        ];

        $this->showSummaryModal = true;
        $this->dispatch('show-summary');
    }

    public function generateLoadsheet()
    {
        WeightBalanceModel::updateOrCreate(['flight_id' => $this->flight->id], ['weights' => $this->calculateWeights()]);
        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully');
    }

    public function render()
    {
        return view('livewire.flight.weight-balance', [
            'weights' => $this->calculateWeights(),
        ]);
    }
}
