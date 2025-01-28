<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\AircraftType;
use Livewire\Component;
use Livewire\WithPagination;

class AircraftTypes extends Component
{
    use WithPagination;

    public Airline $airline;
    public $showForm = false;
    public $search = '';
    public $selectedType;
    public $showCreateForm = false;

    public $form = [
        'code' => '',
        'name' => '',
        'manufacturer' => '',
        'max_passengers' => 0,
        'cargo_capacity' => 0,
        'max_fuel_capacity' => 0,
        'empty_weight' => 0,
        'max_zero_fuel_weight' => 0,
        'max_takeoff_weight' => 0,
        'max_landing_weight' => 0,
        'max_range' => 0,
        'category' => '',
        'max_deck_crew' => 2,
        'max_cabin_crew' => 2,
    ];

    protected $rules = [
        'form.code' => 'required|string|max:10',
        'form.name' => 'required|string|max:255',
        'form.manufacturer' => 'required|string|max:255',
        'form.max_passengers' => 'required|integer|min:0',
        'form.cargo_capacity' => 'required|integer|min:0',
        'form.max_fuel_capacity' => 'required|integer|min:0',
        'form.empty_weight' => 'required|numeric|min:0',
        'form.max_zero_fuel_weight' => 'required|numeric|min:0',
        'form.max_takeoff_weight' => 'required|numeric|min:0',
        'form.max_landing_weight' => 'required|numeric|min:0',
        'form.max_range' => 'required|integer|min:0',
        'form.category' => 'required|string|in:Narrow-body,Wide-body,Regional',
        'form.max_deck_crew' => 'required|integer|min:1',
        'form.max_cabin_crew' => 'required|integer|min:1',
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function render()
    {
        $aircraftTypes = $this->airline->aircraftTypes()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('manufacturer', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('manufacturer')
            ->orderBy('name')
            ->paginate(10);

        $availableTypes = AircraftType::whereNotIn('id', $this->airline->aircraftTypes->pluck('id'))
            ->orderBy('manufacturer')
            ->orderBy('name')
            ->get();

        return view('livewire.airline.aircraft-types', [
            'aircraftTypes' => $aircraftTypes,
            'availableTypes' => $availableTypes
        ]);
    }

    public function createType()
    {
        $this->validate();

        $type = AircraftType::create($this->form);
        $this->airline->aircraftTypes()->attach($type->id);

        $this->reset('form', 'showCreateForm');
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Aircraft type created and added successfully.'
        ]);
    }

    public function addType($typeId)
    {
        $this->airline->aircraftTypes()->attach($typeId);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Aircraft type added successfully.'
        ]);
    }

    public function removeType($typeId)
    {
        // Don't allow removal if aircraft of this type exist
        if ($this->airline->aircraft()->where('aircraft_type_id', $typeId)->exists()) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Cannot remove type - aircraft of this type exist.'
            ]);
            return;
        }

        $this->airline->aircraftTypes()->detach($typeId);
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Aircraft type removed successfully.'
        ]);
    }
}