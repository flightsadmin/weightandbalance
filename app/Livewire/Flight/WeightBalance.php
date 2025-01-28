<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class WeightBalance extends Component
{
    public Flight $flight;
    public $weightBalance;
    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->weightBalance = $flight->weightBalance;
    }

    public function render()
    {
        return view('livewire.flight.weight-balance');
    }
}
