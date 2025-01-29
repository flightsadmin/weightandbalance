<?php

namespace App\Livewire\Aircraft;

use App\Models\Aircraft;
use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $search = '';
    public $airline_id = '';
    public $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'airline_id' => ['except' => ''],
        'status' => ['except' => '']
    ];

    public function render()
    {
        $query = Aircraft::with('airline')
            ->when($this->search, function ($query) {
                $query->whereAny(['registration_number', 'airline.name', 'type', 'model'], 'like', '%' . $this->search . '%');
            })
            ->when($this->airline_id, fn($q) => $q->where('airline_id', $this->airline_id))
            ->when($this->status !== '', fn($q) => $q->where('active', $this->status === 'active'));

        return view('livewire.aircraft.index', [
            'aircraft' => $query->paginate(10),
            'airlines' => Airline::where('active', true)->orderBy('name')->get()
        ])->layout('components.layouts.app');
    }

    public function toggleStatus(Aircraft $aircraft)
    {
        $aircraft->update(['active' => !$aircraft->active]);
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Aircraft status updated successfully.'
        );
    }
}