<?php

namespace App\Livewire\Aircraft;

use App\Models\AircraftType;
use App\Models\Hold;
use Livewire\Component;

class HoldManager extends Component
{
    public AircraftType $aircraftType;
    public $showModal = false;
    public $editingHold = null;

    public $form = [
        'name' => '',
        'code' => '',
        'position' => 1,
        'max_weight' => 0,
        'is_active' => true,
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.code' => 'required|string|max:10',
        'form.position' => 'required|integer|min:1',
        'form.max_weight' => 'required|numeric|min:0',
        'form.is_active' => 'boolean',
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function render()
    {
        return view('livewire.aircraft.hold-manager');
    }

    public function edit(Hold $hold)
    {
        $this->editingHold = $hold;
        $this->form = $hold->toArray();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingHold) {
            $this->editingHold->update($this->form);
        } else {
            $this->aircraftType->holds()->create($this->form);
        }

        $this->reset('form', 'editingHold', 'showModal');
        $this->dispatch('hold-saved');
    }

    public function delete(Hold $hold)
    {
        $hold->delete();
    }
}