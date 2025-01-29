<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $paginationTheme = 'bootstrap';
    public $search = '';
    public $status = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Airline::with('flights')
            ->when($this->search, function ($query) {
                $query->whereAny(['name', 'iata_code', 'address', 'phone', 'email'], 'like', '%' . $this->search . '%');
            })
            ->when($this->status !== '', fn($q) => $q->where('active', $this->status === 'active'));
        return view('livewire.airline.index', [
            'airlines' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(10),
        ])->layout('components.layouts.app', ['title' => 'Airlines']);
    }

    public function toggleStatus(Airline $airline)
    {
        $airline->update(['active' => !$airline->active]);
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Airline status updated successfully.'
        );
    }
}