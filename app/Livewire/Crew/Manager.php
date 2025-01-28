<?php

namespace App\Livewire\Crew;

use App\Models\Crew;
use App\Models\Flight;
use Livewire\Component;

class Manager extends Component
{
    public $search = '';
    public $position = '';
    public $flight;

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function render()
    {
        $query = Crew::query()->with(['flights'])
            ->when($this->position, function ($query) {
                $query->where('position', $this->position);
            });

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereAny(['name', 'employee_id'], 'like', '%' . $this->search . '%')
                    ->orWhereHas('flights', function ($query) {
                        $query->where('flight_number', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->flight) {
            $query->whereHas('flights', function ($q) {
                $q->where('flight_id', $this->flight->id);
            });
        }

        $query->orderByDesc('created_at');

        return view('livewire.flights.crew.manager', [
            'crews' => $query->paginate(10),
        ]);
    }
}

