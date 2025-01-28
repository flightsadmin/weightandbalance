<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Component;

class Show extends Component
{
    public Airline $airline;

    public function mount(Airline $airline)
    {
        $this->airline = $airline->load([
            'aircraft',
            'flights' => function ($query) {
                $query->latest('scheduled_departure_time')->take(5);
            }
        ]);
    }

    public function toggleStatus()
    {
        $this->airline->active = !$this->airline->active;
        $this->airline->save();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Airline status updated successfully.'
        ]);
    }

    public function render()
    {
        return view('livewire.airline.show')->layout('components.layouts.app');
    }
}