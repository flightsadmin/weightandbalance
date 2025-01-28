<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $paginationTheme = 'bootstrap';
    public $search = '';
    public $status = '';
    public $airline_id = '';
    public $aircraft_id = '';

    public function render()
    {
        $query = Flight::with(['airline', 'aircraft'])
            ->when($this->search, function ($query) {
                $query->whereAny(['flight_number', 'departure_airport', 'arrival_airport'], 'like', '%' . $this->search . '%');
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->airline_id, fn($q) => $q->where('airline_id', $this->airline_id))
            ->when($this->aircraft_id, fn($q) => $q->where('aircraft_id', $this->aircraft_id));

        return view('livewire.flight.index', [
            'flights' => $query->paginate(10)
        ])->layout('components.layouts.app');
    }

    public function updateStatus(Flight $flight, $status)
    {
        if (in_array($status, ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled'])) {
            $flight->update(['status' => $status]);
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Flight status updated successfully.'
            ]);
        }
    }
}