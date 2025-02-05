<?php

namespace App\Livewire\Flight;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use Livewire\Component;

class Form extends Component
{
    public ?Flight $flight = null;

    public $airline_id = '';

    public $aircraft_id = '';

    public $flight_number = '';

    public $departure_airport = '';

    public $arrival_airport = '';

    public $scheduled_departure_time = '';

    public $scheduled_arrival_time = '';

    public $status = 'scheduled';

    public $notes = '';

    public function mount(Flight $flight)
    {
        if ($flight->exists) {
            $this->flight = $flight;
            $this->airline_id = $flight->airline_id;
            $this->aircraft_id = $flight->aircraft_id;
            $this->flight_number = $flight->flight_number;
            $this->departure_airport = $flight->departure_airport;
            $this->arrival_airport = $flight->arrival_airport;
            $this->scheduled_departure_time = $flight->scheduled_departure_time->format('Y-m-d\TH:i');
            $this->scheduled_arrival_time = $flight->scheduled_arrival_time->format('Y-m-d\TH:i');
            $this->status = $flight->status;
            $this->notes = $flight->notes;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'airline_id' => 'required|exists:airlines,id',
            'aircraft_id' => 'required|exists:aircraft,id',
            'flight_number' => 'required|string|max:10|unique:flights,flight_number,'.($this->flight?->id ?: 'NULL'),
            'departure_airport' => 'required|string|max:4',
            'arrival_airport' => 'required|string|max:4',
            'scheduled_departure_time' => 'required|date',
            'scheduled_arrival_time' => 'required|date|after:scheduled_departure_time',
            'status' => 'required|string|in:scheduled,boarding,departed,arrived,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($this->flight) {
            $this->flight->update($validated);
            $message = 'Flight updated successfully.';
        } else {
            $this->flight = Flight::create($validated);
            $message = 'Flight created successfully.';
        }

        $this->dispatch(
            'alert',
            icon: 'success',
            message: $message
        );

        return $this->redirect(route('flights.index'), true);
    }

    public function render()
    {
        return view('livewire.flight.form', [
            'airlines' => Airline::orderBy('name')->get(),
            'aircraft' => $this->airline_id
                ? Aircraft::where('airline_id', $this->airline_id)->orderBy('registration_number')->get()
                : collect(),
        ])->layout('components.layouts.app');
    }
}
