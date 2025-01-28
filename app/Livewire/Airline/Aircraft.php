<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class Aircraft extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $search = '';
    public $status = '';
    public Airline $airline;

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function render()
    {
        return view('livewire.airline.aircraft', [
            'aircraft' => $this->airline->aircraft()
                ->with('airline')
                ->when($this->search, function ($query) {
                    $query->where('registration_number', 'like', '%' . $this->search . '%')
                        ->orWhere('type', 'like', '%' . $this->search . '%')
                        ->orWhere('model', 'like', '%' . $this->search . '%');
                })
                ->when($this->status, fn($q) => $q->where('active', $this->status === 'active'))
                ->latest()
                ->paginate(10)
        ])->layout('components.layouts.app');
    }
}