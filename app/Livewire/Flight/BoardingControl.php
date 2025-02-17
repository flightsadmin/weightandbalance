<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;
use Livewire\WithPagination;

class BoardingControl extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Flight $flight;

    public $activeTab = 'seat';

    public $seatNumber = '';

    public $selectedPassengers = [];

    public $search = '';

    public $selectAll = false;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load(['passengers.seat', 'aircraft.type.cabinZones.seats']);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->reset(['selectedPassengers', 'selectAll', 'search']);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPassengers = $this->flight->passengers()
                ->where('acceptance_status', 'accepted')
                ->where('boarding_status', '!=', 'boarded')
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->whereAny(['name', 'ticket_number'], 'like', "%{$this->search}%")
                )
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedPassengers = [];
        }
    }

    public function updatedSelectedPassengers()
    {
        $this->selectAll = false;
    }

    public function boardBySeat()
    {
        $passenger = $this->flight->passengers()
            ->whereHas('seat', fn($q) => $q->where('designation', strtoupper($this->seatNumber)))
            ->where('acceptance_status', 'accepted')
            ->where('boarding_status', '!=', 'boarded')
            ->first();

        if (!$passenger) {
            $this->dispatch('alert', icon: 'error', message: 'No accepted passenger found for seat ' . $this->seatNumber);
            return;
        }

        $passenger->update(['boarding_status' => 'boarded']);
        $this->seatNumber = '';
        $this->dispatch('alert', icon: 'success', message: 'Passenger boarded successfully');
    }

    public function boardSelected()
    {
        $count = $this->flight->passengers()
            ->whereIn('id', $this->selectedPassengers)
            ->update(['boarding_status' => 'boarded']);

        $this->selectedPassengers = [];
        $this->selectAll = false;
        $this->dispatch('alert', icon: 'success', message: $count . ' passengers boarded successfully');
    }

    public function unboardPassenger($passengerId)
    {
        $this->flight->passengers()
            ->where('id', $passengerId)
            ->update(['boarding_status' => 'unboarded']);

        $this->dispatch('alert', icon: 'success', message: 'Passenger unboarded successfully');
    }

    public function render()
    {
        $query = $this->flight->passengers()
            ->with('seat')
            ->where('acceptance_status', 'accepted');

        if ($this->activeTab === 'list' || $this->activeTab === 'seat') {
            $query->where('boarding_status', '!=', 'boarded');
        } elseif ($this->activeTab === 'boarded') {
            $query->where('boarding_status', 'boarded');
        }

        if ($this->activeTab === 'list') {
            $query->when(
                $this->search,
                fn($q) =>
                $q->whereAny(['name', 'ticket_number'], 'like', "%{$this->search}%")
            );
        }

        $query->orderBy('seat_id');

        return view('livewire.flights.boarding-control', [
            'passengers' => $query->paginate(10),
            'boardedCount' => $this->flight->passengers()->where('boarding_status', 'boarded')->count(),
            'totalCount' => $this->flight->passengers()->where('acceptance_status', 'accepted')->count(),
        ]);
    }
}