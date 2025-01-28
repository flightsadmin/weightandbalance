<?php

namespace App\Livewire\Aircraft;

use App\Models\Aircraft;
use Livewire\Component;

class Show extends Component
{
    public Aircraft $aircraft;

    public function mount(Aircraft $aircraft)
    {
        $this->aircraft = $aircraft->load([
            'airline',
            'flights' => function ($query) {
                $query->latest('scheduled_departure_time')->take(5);
            }
        ]);
    }

    public function toggleStatus()
    {
        $this->aircraft->active = !$this->aircraft->active;
        $this->aircraft->save();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Aircraft status updated successfully.'
        ]);
    }

    public function render()
    {
        return view('livewire.aircraft.show')->layout('components.layouts.app');
    }
}