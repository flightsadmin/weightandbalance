<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;
use Livewire\Attributes\On;

class Overview extends Component
{
    public Flight $flight;

    public $showTimeModal = false;
    public $showRegistrationModal = false;
    public $timeType = '';
    public $selectedFlight = null;
    public $timeForm = [
        'datetime' => '',
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load(['airline', 'aircraft', 'crew', 'passengers', 'baggage', 'cargo']);
    }

    public function updateStatus($status)
    {
        $this->flight->update(['status' => $status]);
    }

    public function updateRegistration($reg)
    {
        $this->flight->update(['aircraft_id' => $reg]);
    }

    public function openTimeModal($flightId, $type)
    {
        $this->selectedFlight = Flight::find($flightId);
        $this->timeType = $type;
        $this->timeForm['datetime'] = $type === 'ATD'
            ? $this->selectedFlight->scheduled_departure_time->format('Y-m-d H:i')
            : $this->selectedFlight->scheduled_arrival_time->format('Y-m-d H:i');
        $this->showTimeModal = true;
    }

    public function updateFlightTime()
    {
        $this->validate([
            'timeForm.datetime' => 'required|after:scheduled_departure_time',
        ]);

        if (!$this->selectedFlight) {
            $this->dispatch('alert', icon: 'danger', message: 'Flight not found');
            return;
        }

        if ($this->timeType === 'ATD') {
            $this->selectedFlight->update([
                'actual_departure_time' => $this->timeForm['datetime'],
                'status' => 'departed'
            ]);
            $message = 'Departure time updated successfully';
        } else {
            $this->selectedFlight->update([
                'actual_arrival_time' => $this->timeForm['datetime'],
                'status' => 'arrived'
            ]);
            $message = 'Arrival time updated successfully';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('time-updated');
        $this->reset(['showTimeModal', 'timeForm', 'selectedFlight', 'timeType']);
    }

    #[On('time-updated')]
    public function render()
    {
        return view('livewire.flights.overview')->layout('components.layouts.app');
    }
}
