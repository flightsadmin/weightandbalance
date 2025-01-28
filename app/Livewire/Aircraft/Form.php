<?php

namespace App\Livewire\Aircraft;

use App\Models\Aircraft;
use App\Models\Airline;
use Livewire\Component;

class Form extends Component
{
    public ?Aircraft $aircraft = null;

    public $airline_id = '';
    public $registration = '';
    public $type = '';
    public $model = '';
    public $passenger_capacity = '';
    public $cargo_capacity = '';
    public $empty_weight = '';
    public $max_takeoff_weight = '';
    public $max_fuel_capacity = '';
    public $active = true;
    public $notes = '';

    public function mount(Aircraft $aircraft)
    {
        if ($aircraft->exists) {
            $this->aircraft = $aircraft;
            $this->airline_id = $aircraft->airline_id;
            $this->registration = $aircraft->registration;
            $this->type = $aircraft->type;
            $this->model = $aircraft->model;
            $this->passenger_capacity = $aircraft->passenger_capacity;
            $this->cargo_capacity = $aircraft->cargo_capacity;
            $this->empty_weight = $aircraft->empty_weight;
            $this->max_takeoff_weight = $aircraft->max_takeoff_weight;
            $this->max_fuel_capacity = $aircraft->max_fuel_capacity;
            $this->active = $aircraft->active;
            $this->notes = $aircraft->notes;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'airline_id' => 'required|exists:airlines,id',
            'registration' => 'required|string|max:10|unique:aircraft,registration,' . ($this->aircraft?->id ?: 'NULL'),
            'type' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'passenger_capacity' => 'required|integer|min:1',
            'cargo_capacity' => 'required|numeric|min:0',
            'empty_weight' => 'required|numeric|min:0',
            'max_takeoff_weight' => 'required|numeric|min:0',
            'max_fuel_capacity' => 'required|numeric|min:0',
            'active' => 'required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($this->aircraft) {
            $this->aircraft->update($validated);
            $message = 'Aircraft updated successfully.';
        } else {
            $this->aircraft = Aircraft::create($validated);
            $message = 'Aircraft created successfully.';
        }

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $message
        ]);

        return $this->redirect(route('aircraft.index'), true);
    }

    public function render()
    {
        return view('livewire.aircraft.form', [
            'airlines' => Airline::where('active', true)->orderBy('name')->get()
        ])->layout('components.layouts.app');
    }
}