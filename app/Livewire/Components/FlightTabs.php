<?php

namespace App\Livewire\Components;

use Livewire\Component;

class FlightTabs extends Component
{
    public $activeTab;
    public $flight;

    public function mount()
    {
        $this->activeTab = session('flight_tab', 'overview');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        session(['flight_tab' => $tab]);
    }

    public function render()
    {
        $this->flight->loadAllCounts();

        return view('livewire.components.flight-tabs', [
            'baggage_count' => $this->flight->baggage_count,
            'cargo_count' => $this->flight->cargo_count,
            'passengers_count' => $this->flight->passengers_count,
            'crew_count' => $this->flight->crew_count,
            'containers_count' => $this->flight->containers_count,
        ]);
    }
}