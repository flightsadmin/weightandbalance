<?php

namespace App\Livewire\Flight;

use Livewire\Component;
use App\Models\Flight;

class Container extends Component
{
    public $flight;

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function render()
    {

        return view('livewire.flights.container.containers', [
            'containers' => $this->flight->containers()->latest()->paginate(10),
        ]);


    }
}
