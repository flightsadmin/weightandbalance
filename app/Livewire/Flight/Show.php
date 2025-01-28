<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\WeightBalance;
use Livewire\Component;

class Show extends Component
{
    public Flight $flight;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load(['airline', 'aircraft', 'crew', 'passengers', 'baggage', 'cargo', 'fuel']);
    }

    public function render()
    {
        return view('livewire.flight.show')->layout('components.layouts.app');
    }
}