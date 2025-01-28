<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class Flights extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $search = '';
    public $status = '';
    public Airline $airline;

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function render()
    {
        return view('livewire.airline.flights', [
            'flights' => $this->airline->flights()
                ->with(['aircraft'])
                ->when($this->search, function ($query) {
                    $query->where('flight_number', 'like', '%' . $this->search . '%')
                        ->orWhere('departure_airport', 'like', '%' . $this->search . '%')
                        ->orWhere('arrival_airport', 'like', '%' . $this->search . '%');
                })
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->latest('scheduled_departure_time')
                ->paginate(10)
        ])->layout('components.layouts.app');
    }
}