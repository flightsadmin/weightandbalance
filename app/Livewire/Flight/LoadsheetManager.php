<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\Setting;
use Livewire\Component;

class LoadsheetManager extends Component
{
    public Flight $flight;

    public $loadsheet;

    public $loadplan;

    public $showModal = false;

    public $paxDistribution = [];
    public $editingZone = null;

    public $zoneForm = [
        'male' => 0,
        'female' => 0,
        'child' => 0,
        'infant' => 0
    ];

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

        // Check for persisted manual distribution first
        $this->initializePaxDistribution();
    }

    protected function initializePaxDistribution()
    {
        // First check for persisted manual distribution
        $manualDistribution = $this->flight->settings()
            ->where('key', 'manual_pax_distribution')
            ->first();

        if ($manualDistribution) {
            $this->paxDistribution = $manualDistribution->typed_value;
            return;
        }

        // Fallback to loadsheet or actual passenger data
        if ($this->loadsheet && isset($this->loadsheet->distribution['load_data']['pax_by_zone'])) {
            $this->paxDistribution = $this->loadsheet->distribution['load_data']['pax_by_zone'];
        } else {
            $this->initializeEmptyDistribution();
            $this->updatePaxDistributionFromPassengers();
        }
    }

    protected function initializeEmptyDistribution()
    {
        $zones = $this->flight->aircraft->type->cabinZones;

        foreach ($zones as $zone) {
            $this->paxDistribution[$zone->name] = [
                'male' => 0,
                'female' => 0,
                'child' => 0,
                'infant' => 0,
                'max_pax' => $zone->max_capacity
            ];
        }
    }

    protected function updatePaxDistributionFromPassengers()
    {
        $passengers = $this->flight->passengers()
            ->with('seat.cabinZone')
            ->get();

        foreach ($passengers as $passenger) {
            if ($passenger->seat && $passenger->seat->cabinZone) {
                $zoneName = $passenger->seat->cabinZone->name;
                $this->paxDistribution[$zoneName][$passenger->type]++;
            }
        }
    }

    public function editZone($zoneName)
    {
        $this->editingZone = $zoneName;
        $this->zoneForm = [
            'male' => $this->paxDistribution[$zoneName]['male'],
            'female' => $this->paxDistribution[$zoneName]['female'],
            'child' => $this->paxDistribution[$zoneName]['child'],
            'infant' => $this->paxDistribution[$zoneName]['infant']
        ];
    }

    public function saveZoneDistribution()
    {
        $this->validate([
            'zoneForm.male' => 'required|integer|min:0',
            'zoneForm.female' => 'required|integer|min:0',
            'zoneForm.child' => 'required|integer|min:0',
            'zoneForm.infant' => 'required|integer|min:0'
        ]);

        $total = array_sum($this->zoneForm);
        $maxPax = $this->paxDistribution[$this->editingZone]['max_pax'];

        if ($total - $this->zoneForm['infant'] > $maxPax) {
            $this->dispatch('alert', icon: 'error', message: "Total passengers exceeds zone capacity of {$maxPax}");
            return;
        }

        $this->paxDistribution[$this->editingZone] = array_merge(
            $this->paxDistribution[$this->editingZone],
            $this->zoneForm
        );

        // Persist the manual distribution to flight settings
        $this->flight->settings()->updateOrCreate(
            [
                'key' => 'manual_pax_distribution',
                'airline_id' => $this->flight->airline_id
            ],
            [
                'value' => json_encode($this->paxDistribution),
                'type' => 'json',
                'description' => 'Actual PAX distribution - ' . $this->flight->flight_number
            ]
        );

        if ($this->loadsheet) {
            $distribution = $this->loadsheet->distribution;
            $distribution['load_data']['pax_by_zone'] = $this->paxDistribution;
            $this->loadsheet->update(['distribution' => $distribution]);
        }

        $this->editingZone = null;
        $this->dispatch('alert', icon: 'success', message: 'Passenger distribution updated and persisted.');
    }

    public function resetDistribution()
    {
        // Delete the persisted manual distribution
        $this->flight->settings()->where('key', 'manual_pax_distribution')->delete();

        // Reset to actual passenger distribution
        $this->initializeEmptyDistribution();
        $this->updatePaxDistributionFromPassengers();

        // Update loadsheet if exists
        if ($this->loadsheet) {
            $distribution = $this->loadsheet->distribution;
            $distribution['load_data']['pax_by_zone'] = $this->paxDistribution;
            $this->loadsheet->update(['distribution' => $distribution]);
        }

        $this->dispatch('alert', icon: 'success', message: 'Distribution reset to actual passenger data.');
    }

    private function calculateTotalDeadload()
    {
        return array_sum(array_column($this->generateLoadData()['hold_breakdown'], 'weight'));
    }

    private function calculateTotalTrafficLoad()
    {
        return $this->calculateTotalDeadload() + array_sum(array_column($this->generateLoadData()['pax_by_type'], 'weight'));
    }

    private function calculateDryOperatingWeight()
    {
        return $this->flight->aircraft->basic_weight + $this->calculateCrewIndexes()['weight'] + $this->calculatePantryIndex()['weight'];
    }

    private function calculateZeroFuelWeight()
    {
        return $this->calculateDryOperatingWeight() + $this->calculateTotalTrafficLoad();
    }

    private function calculateTakeoffWeight()
    {
        return $this->calculateZeroFuelWeight() + $this->flight->fuel->take_off_fuel;
    }

    private function calculateLandingWeight()
    {
        return $this->calculateTakeoffWeight() - $this->flight->fuel->trip_fuel;
    }

    private function calculateUnderload()
    {
        return min(
            $this->flight->aircraft->type->max_zero_fuel_weight - $this->calculateZeroFuelWeight(),
            $this->flight->aircraft->type->max_takeoff_weight - $this->calculateTakeoffWeight(),
            $this->flight->aircraft->type->max_landing_weight - $this->calculateLandingWeight()
        );
    }

    private function calculatePantryIndex()
    {
        if (!$this->flight->fuel?->pantry) {
            $this->dispatch('alert', icon: 'error', message: 'No pantry code found.');
            return;
        }

        return $this->flight->aircraft->type->getPantryDetails($this->flight->fuel->pantry);
    }

    private function calculateFuel()
    {
        return [
            'block' => $this->flight->fuel->block_fuel,
            'taxi' => $this->flight->fuel->taxi_fuel,
            'trip' => $this->flight->fuel->trip_fuel,
            'takeoff' => $this->flight->fuel->take_off_fuel,
            'crew' => $this->flight->fuel->crew,
        ];
    }

    public function finalizeLoadsheet()
    {
        if (!$this->loadsheet) {
            $this->dispatch('alert', icon: 'error', message: 'No loadsheet found to finalize.');
            return;
        }

        $this->loadsheet->update([
            'final' => true,
            'status' => 'released',
            'released_by' => auth()->id(),
            'released_at' => now(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet finalized successfully.');
    }

    public function revokeLoadsheet()
    {
        if (!$this->loadsheet) {
            $this->dispatch('alert', icon: 'error', message: 'No loadsheet found to revoke.');
            return;
        }

        if (!$this->loadsheet->final) {
            $this->dispatch('alert', icon: 'error', message: 'Only finalized loadsheets can be revoked.');
            return;
        }

        $this->loadsheet->update([
            'status' => 'revoked',
            'final' => false,
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet revoked successfully.');
    }

    private function getPassengerDistribution()
    {
        // Check for persisted manual distribution first
        $manualDistribution = $this->flight->settings()->where('key', 'manual_pax_distribution')->first();

        if ($manualDistribution) {
            return $manualDistribution->typed_value;
        }

        // Otherwise calculate from actual passengers
        $distribution = [];
        $zones = $this->flight->aircraft->type->cabinZones;

        foreach ($zones as $zone) {
            $distribution[$zone->name] = [
                'male' => 0,
                'female' => 0,
                'child' => 0,
                'infant' => 0,
                'max_pax' => $zone->max_capacity
            ];
        }

        $passengers = $this->flight->passengers()
            ->with('seat.cabinZone')
            ->get();

        foreach ($passengers as $passenger) {
            if ($passenger->seat && $passenger->seat->cabinZone) {
                $zoneName = $passenger->seat->cabinZone->name;
                $distribution[$zoneName][$passenger->type]++;
            }
        }
        return $distribution;
    }

    private function generateLoadData()
    {
        $pax = ['male', 'female', 'child', 'infant'];
        $paxDistribution = $this->getPassengerDistribution();

        $paxByType = [
            'male' => ['count' => 0, 'weight' => 0],
            'female' => ['count' => 0, 'weight' => 0],
            'child' => ['count' => 0, 'weight' => 0],
            'infant' => ['count' => 0, 'weight' => 0]
        ];

        foreach ($paxDistribution as $zone) {
            foreach ($pax as $type) {
                $paxByType[$type]['count'] += $zone[$type];
                $paxByType[$type]['weight'] += $zone[$type] * $this->flight->airline->getStandardPassengerWeight($type);
            }
        }

        $orderedWeightsUsed = collect($pax)->mapWithKeys(fn($type) => [
            $type => $this->flight->airline->getStandardPassengerWeight($type),
        ])->toArray();

        return [
            'pax_by_zone' => $paxDistribution,
            'pax_by_type' => $paxByType,
            'passenger_weights_used' => $orderedWeightsUsed,
            'hold_breakdown' => $this->flight->aircraft->type->holds()
                ->with('positions')->get()
                ->map(function ($hold) {
                    $containers = $this->flight->containers()
                        ->whereIn('position_id', $hold->positions->pluck('id'))->get();

                    $weight = $containers->sum('weight');

                    return [
                        'hold_no' => $hold->code,
                        'weight' => $weight,
                        'index' => round($weight * $hold->index, 2),
                    ];
                })->filter(fn($hold) => $hold['weight'] > 0)->values()->toArray(),
            'deadload_by_type' => [
                'C' => [
                    'pieces' => $this->flight->cargo->where('status', 'loaded')->whereNotNull('container_id')->sum('pieces'),
                    'weight' => $this->flight->cargo->where('status', 'loaded')->whereNotNull('container_id')->sum('weight'),
                ],
                'B' => [
                    'pieces' => $this->flight->baggage->where('status', 'loaded')->whereNotNull('container_id')->count(),
                    'weight' => $this->flight->baggage->where('status', 'loaded')->whereNotNull('container_id')->sum('weight'),
                ],
                'M' => [
                    'pieces' => 0,
                    'weight' => 0,
                ],
                'O' => [
                    'pieces' => 0,
                    'weight' => 0,
                ],
            ],
        ];
    }

    private function generateTrimData()
    {
        $envelopes = $this->flight->aircraft->type->envelopes()
            ->where('is_active', true)
            ->where('name', '!=', 'FUEL')
            ->get()->mapWithKeys(function ($envelope) {
                $points = collect($envelope->points)->map(function ($point) {
                    return [
                        'x' => $point['index'],
                        'y' => $point['weight'],
                    ];
                })->values()->toArray();

                return [strtolower($envelope->name) . 'Envelope' => $points];
            })
            ->toArray();

        return $envelopes;
    }

    public function generateLoadsheet()
    {
        if (!$this->flight->fuel) {
            $this->dispatch('alert', icon: 'error', message: 'Fuel data must be added before generating loadsheet.');
            return;
        }

        if (!$this->loadplan || $this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Load plan must be released before generating loadsheet.');
            return;
        }

        $loadData = $this->generateLoadData();

        $this->loadsheet = $this->flight->loadsheets()->create([
            'version' => ($this->loadsheet?->version ?? 0) + 1,
            'distribution' => [
                'load_data' => $loadData,
                'trim_data' => $this->generateTrimData(),
                'flight' => $this->generateFlightData(),
                'fuel' => $this->calculateFuel(),
                'weights' => [
                    'dry_operating_weight' => $this->calculateDryOperatingWeight(),
                    'zero_fuel_weight' => $this->calculateZeroFuelWeight(),
                    'takeoff_weight' => $this->calculateTakeoffWeight(),
                    'landing_weight' => $this->calculateLandingWeight(),
                ],
                'indices' => $this->calculateIndices(),
            ],
            'status' => 'draft'
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
        return redirect()->route('flights.show', $this->flight->id);
    }

    private function generateFlightData()
    {
        return [
            'flight_number' => $this->flight->flight_number,
            'flight_date' => strtoupper($this->flight->scheduled_departure_time?->format('dMY')),
            'short_flight_date' => $this->flight->scheduled_departure_time?->format('d'),
            'registration' => $this->flight->aircraft->registration_number,
            'destination' => $this->flight->arrival_airport,
            'sector' => $this->flight->departure_airport . '/' . $this->flight->arrival_airport,
            'version' => $this->flight->aircraft->type->code,
            'release_time' => now('Asia/Qatar')->format('Hi'),
            'underload' => $this->calculateUnderload(),
            'total_deadload' => $this->calculateTotalDeadload(),
            'total_traffic_load' => $this->calculateTotalTrafficLoad(),
        ];
    }

    private function calculateIndices()
    {
        $aircraft = $this->flight->aircraft;
        $type = $aircraft->type;
        $fuel = $this->flight->fuel;

        $crewIndexes = $this->calculateCrewIndexes();
        $fuelIndexes = $type->getFuelIndexes($fuel->take_off_fuel, $fuel->take_off_fuel - $fuel->trip_fuel);

        $paxData = $this->generateLoadData()['pax_by_type'];
        $cargoData = $this->generateLoadData()['hold_breakdown'];

        $indices = [
            'pantry' => $this->calculatePantryIndex(),
            'basic_index' => $aircraft->basic_index,
            'crew_index' => $crewIndexes['index'],
            'pax_index' => array_sum(array_column($paxData, 'index')),
            'cargo_index' => array_sum(array_column($cargoData, 'index')),
            'litof' => $fuelIndexes['takeoff'],
            'lildf' => $fuelIndexes['landing'],
        ];

        $indices['doi'] = $indices['basic_index'] + $indices['pantry']['index'] + $indices['crew_index'];
        $indices['dli'] = $indices['doi'] + $indices['cargo_index'];
        $indices['lizfw'] = $indices['dli'] + $indices['pax_index'];
        $indices['litow'] = $indices['lizfw'] + $fuelIndexes['takeoff'];
        $indices['lildw'] = $indices['litow'] + $fuelIndexes['landing'];
        $indices['maczfw'] = $type->getZfwMac($this->calculateZeroFuelWeight(), $indices['lizfw']);
        $indices['mactow'] = $type->getTowMac($this->calculateTakeoffWeight(), $indices['litow']);
        $indices['macldw'] = $type->getLdwMac($this->calculateLandingWeight(), $indices['lildw']);

        foreach ($indices as &$value) {
            if (is_numeric($value)) {
                $value = number_format($value, 2);
            }
        }
        unset($value);

        return $indices;
    }

    public function calculateCrewIndexes()
    {
        $crewConfig = $this->flight->fuel->crew;
        if (!$crewConfig) {
            $this->dispatch('alert', icon: 'error', message: 'No crew configuration found.');
            return ['index' => 0, 'weight' => 0];
        }
        [$deckCrew, $cabinCrew] = explode('/', $crewConfig);
        $deckCrewCount = (int) $deckCrew;
        $cabinCrewCount = (int) $cabinCrew;

        $crewCalculation = $this->flight->aircraft->type->calculateCrewIndex($deckCrewCount, $cabinCrewCount);

        if (isset($crewCalculation['error'])) {
            $this->dispatch('alert', icon: 'error', message: $crewCalculation['error']);
            return;
        }
        return [
            'index' => $crewCalculation['index'],
            'weight' => $crewCalculation['weight']
        ];
    }

    public function render()
    {
        return view('livewire.flights.loadsheet-manager');
    }
}
