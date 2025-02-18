<?php

namespace App\Livewire\Passenger;

use App\Models\Baggage;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Seat;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Flight $flight;

    public $passenger;

    public $showForm = false;

    public $editingPassenger = null;

    public $editingBaggage = null;

    public $search = '';

    public $pieces = '';

    public $weight = '';

    public $form = [
        'name' => '',
        'ticket_number' => '',
        'type' => '',
    ];

    public $selectedPassenger = null;

    public $showPassengerModal = false;

    public $selectedSeat = null;

    public $seatsByZone = [];

    public $seatForm = [
        'seat_id' => null,
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.ticket_number' => 'nullable|string|max:255',
        'form.type' => 'required|in:male,female,child,infant',
        'seatForm.seat_id' => 'nullable|exists:seats,id',
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load([
            'passengers.seat',
            'aircraft.type.cabinZones.seats'
        ])->loadCount([
                    'passengers',
                    'passengers as accepted_count' => function ($query) {
                        $query->where('acceptance_status', 'accepted');
                    },
                    'passengers as standby_count' => function ($query) {
                        $query->where('acceptance_status', 'standby');
                    },
                    'passengers as offloaded_count' => function ($query) {
                        $query->where('acceptance_status', 'offloaded');
                    }
                ]);
    }

    public function loadSeats()
    {
        if ($this->flight) {
            return $this->flight->aircraft->type->cabinZones()
                ->with([
                    'seats' => function ($query) {
                        $query->orderBy('row')
                            ->orderBy('column')
                            ->withCount([
                                'passenger as is_occupied' => function ($query) {
                                    $query->where('flight_id', $this->flight->id);
                                }
                            ])
                            ->withExists([
                                'flights as is_blocked' => function ($query) {
                                    $query->where('flights.id', $this->flight->id)
                                        ->where('flight_seats.is_blocked', true);
                                }
                            ]);
                    }
                ])
                ->get()
                ->map(function ($zone) {
                    $zone->seats = $zone->seats->map(function ($seat) {
                        return $seat;
                    });
                    return $zone;
                });
        }

        return collect();
    }

    public function edit(Passenger $passenger)
    {
        $this->passenger = $passenger;
        $this->form = $passenger->only(['name', 'ticket_number', 'type']);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $this->flight->passengers()->create([
            'name' => $this->form['name'],
            'ticket_number' => $this->form['ticket_number'],
            'type' => $this->form['type'],
            'acceptance_status' => 'pending',
            'boarding_status' => 'unboarded',
        ]);

        $this->reset('form', 'showForm');
        $this->dispatch('alert', icon: 'success', message: 'Passenger added successfully.');
        $this->dispatch('passenger-saved');
    }

    public function update()
    {
        $this->validate();

        $this->passenger->update($this->form);
        $this->reset('form', 'showForm', 'passenger');
        $this->dispatch('alert', icon: 'success', message: 'Passenger updated successfully.');
        $this->dispatch('passenger-saved');
    }

    public function delete(Passenger $passenger)
    {
        $passenger->delete();
        $this->dispatch('alert', icon: 'success', message: 'Passenger deleted successfully.');
        $this->dispatch('passenger-saved');
    }

    public function updateAcceptanceStatus(Passenger $passenger, $status)
    {
        $passenger->update([
            'acceptance_status' => $status,
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: ucfirst($status) . ' passenger successfully.'
        );
        $this->dispatch('passenger-saved');
    }

    public function saveBaggage()
    {
        $this->editingPassenger->baggage()->delete();
        for ($i = 0; $i < $this->pieces; $i++) {
            $this->editingPassenger->baggage()->create([
                'flight_id' => $this->editingPassenger->flight->id,
                'tag_number' => $this->editingPassenger->flight->airline->iata_code . str_pad(Baggage::max('id') + 1, 6, '0', STR_PAD_LEFT),
                'weight' => $this->weight / $this->pieces,
            ]);
        }
        $this->dispatch('alert', icon: 'success', message: 'Baggage saved successfully.');
        $this->dispatch('baggage-saved');
        $this->dispatch('passenger-saved');
    }

    public function editBaggage(Passenger $passenger)
    {
        $this->editingPassenger = $passenger;
        $this->pieces = $passenger->baggage->count();
        $this->weight = $passenger->baggage->sum('weight');
    }

    public function showPassengerDetails(Passenger $passenger)
    {
        $this->selectedPassenger = $passenger->load([
            'baggage' => function ($query) {
                $query->with('container')->latest();
            },
        ]);
    }

    public function showSeatModal(Passenger $passenger)
    {
        $this->editingPassenger = $passenger->load('seat');
        $this->selectedSeat = $passenger->seat_id;
        $this->seatForm['seat_id'] = $passenger->seat_id;
    }

    public function selectSeat($seatId)
    {
        $this->selectedSeat = $seatId;
        $this->seatForm['seat_id'] = $seatId;
    }

    public function assignSeat()
    {
        if (!$this->editingPassenger) {
            $this->dispatch('alert', icon: 'error', message: 'No passenger selected.');
            return;
        }

        if (!$this->selectedSeat) {
            $this->dispatch('alert', icon: 'error', message: 'Please select a seat.');
            return;
        }

        $seat = Seat::findOrFail($this->selectedSeat);

        if (!$seat->isAvailable($this->flight)) {
            $this->dispatch('alert', icon: 'error', message: 'This seat is not available.');
            return;
        }

        if (!$this->flight->seats()->where('seat_id', $seat->id)->exists()) {
            $this->flight->seats()->attach($seat->id, [
                'is_blocked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $passenger = Passenger::findOrFail($this->editingPassenger['id']);
        $passenger->update([
            'seat_id' => $seat->id
        ]);

        $this->reset(['seatForm', 'selectedSeat']);
        $this->editingPassenger = null;

        $this->dispatch('seat-saved');
        $this->dispatch('alert', icon: 'success', message: 'Seat assigned successfully.');
        $this->dispatch('passenger-saved');
    }

    public function removeSeat(Passenger $passenger)
    {
        $passenger->update(['seat_id' => null]);
        $this->dispatch('alert', icon: 'success', message: 'Seat assignment removed.');
        $this->dispatch('passenger-saved');
    }

    public function blockSeat($seatId)
    {
        // Check if the relationship doesn't exist before attaching
        if (!$this->flight->seats()->where('seat_id', $seatId)->exists()) {
            $this->flight->seats()->attach($seatId, [
                'is_blocked' => true,
                'blocked_reason' => 'Blocked by staff',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->flight->seats()->updateExistingPivot($seatId, [
                'is_blocked' => true,
                'blocked_reason' => 'Blocked by staff',
            ]);
        }

        $this->dispatch('alert', icon: 'success', message: 'Seat blocked successfully.');
        $this->dispatch('passenger-saved');
    }

    public function unblockSeat($seatId)
    {
        $this->flight->seats()->updateExistingPivot($seatId, [
            'is_blocked' => false,
            'blocked_reason' => null,
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Seat unblocked successfully.');
        $this->dispatch('passenger-saved');
    }

    #[On('passenger-saved')]
    public function render()
    {
        return view('livewire.flights.passenger.manager', [
            'passengers' => $this->flight->passengers()
                ->with('seat')
                ->withCount('baggage')
                ->paginate(10),
            'seats' => $this->loadSeats(),
        ]);
    }
}
