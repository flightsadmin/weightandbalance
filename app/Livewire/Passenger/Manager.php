<?php

namespace App\Livewire\Passenger;

use App\Models\Flight;
use App\Models\Passenger;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Manager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $flight = null;
    public $showForm = false;
    public $editingPassenger = null;
    public $search = '';

    public $form = [
        'name' => '',
        'seat_number' => '',
        'ticket_number' => '',
        'type' => '',
        'notes' => ''
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.seat_number' => 'required|string|max:4',
        'form.ticket_number' => 'nullable|string|max:255',
        'form.type' => 'required|in:male,female,child,infant',
        'form.notes' => 'nullable|string'
    ];

    public function mount(?Flight $flight = null)
    {
        $this->flight = $flight;
    }

    public function edit(Passenger $passenger)
    {
        $this->editingPassenger = $passenger;
        $this->form = $passenger->only([
            'name',
            'seat_number',
            'ticket_number',
            'type',
            'notes'
        ]);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = array_merge($this->form, [
            'flight_id' => $this->flight?->id
        ]);

        if ($this->editingPassenger) {
            $this->editingPassenger->update($data);
        } else {
            Passenger::create($data);
        }

        $this->reset('form', 'editingPassenger', 'showForm');
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Passenger saved successfully.'
        );
        $this->dispatch('passenger-saved');
    }

    public function delete(Passenger $passenger)
    {
        $passenger->delete();
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Passenger removed successfully.'
        );
    }

    public function updateAcceptanceStatus($passengerId, $status)
    {
        $passenger = Passenger::findOrFail($passengerId);
        $passenger->update([
            'acceptance_status' => $status
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: ucfirst($status) . ' passenger successfully.'
        );
    }

    public function updateBoardingStatus($passengerId, $status)
    {
        $passenger = Passenger::findOrFail($passengerId);
        $passenger->update([
            'boarding_status' => $status
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: ucfirst($status) . ' passenger successfully.'
        );
    }

    #[On('passenger-saved')]
    public function render()
    {
        $query = Passenger::query()
            ->with(['flight', 'baggage'])
            ->withCount('baggage');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('ticket_number', 'like', '%' . $this->search . '%')
                    ->orWhere('seat_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->flight) {
            $query->whereHas('flight', function ($q) {
                $q->where('flight_id', $this->flight->id);
            });
        }

        $query->orderByDesc('created_at');

        return view('livewire.flights.passenger.manager', [
            'passengers' => $query->paginate(10)
        ]);
    }
}