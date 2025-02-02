<?php

namespace App\Livewire\Passenger;

use App\Models\Baggage;
use App\Models\Flight;
use App\Models\Passenger;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $flight = null;
    public $showForm = false;
    public $editingPassenger = null;
    public $editingBaggage = null;
    public $search = '';
    public $pieces = '';
    public $weight = '';
    public $form = [
        'name' => '',
        'seat_number' => '',
        'ticket_number' => '',
        'type' => '',
        'notes' => ''
    ];

    public $selectedPassenger = null;
    public $showPassengerModal = false;

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
    public function saveBaggage()
    {
        $this->editingPassenger->baggage()->delete();
        for ($i = 0; $i < $this->pieces; $i++) {
            $this->editingPassenger->baggage()->create([
                'flight_id' => $this->editingPassenger->flight->id,
                'tag_number' => $this->editingPassenger->flight->airline->iata_code . str_pad(Baggage::max('id') + 1, 6, '0', STR_PAD_LEFT),
                'weight' => $this->weight / $this->pieces
            ]);
        }
        $this->dispatch('alert', icon: 'success', message: 'Baggage saved successfully.');
        $this->dispatch('baggage-saved');
    }

    public function editBaggage(Passenger $passenger)
    {
        $this->editingPassenger = $passenger;
        $this->pieces = $passenger->baggage->count();
        $this->weight = $passenger->baggage->sum('weight');
    }

    public function showPassengerDetails($passengerId)
    {
        $this->selectedPassenger = $this->flight->passengers()
            ->with([
                'baggage' => function ($query) {
                    $query->with('container')->latest();
                }
            ])
            ->find($passengerId);

        $this->dispatch('show-passenger-modal');
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