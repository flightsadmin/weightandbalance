<?php

namespace App\Livewire\Fuel;

use App\Models\Fuel;
use App\Models\Flight;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $flight;

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function render()
    {
        return view('livewire.flights.fuel.manager', [
            'fuel' => $this->flight->fuel,
        ]);
    }
}
