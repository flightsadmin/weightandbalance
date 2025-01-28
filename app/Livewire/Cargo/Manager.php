<?php

namespace App\Livewire\Cargo;

use Livewire\Component;
use App\Models\Flight;
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
        return view('livewire.flights.cargo.manager', [
            'cargo' => $this->flight->cargo()->with('container')->latest()->paginate(20),
            'containers' => $this->flight->containers()->where('type', 'cargo')->latest()->paginate(20),
        ])->layout('components.layouts.app');
    }
}
