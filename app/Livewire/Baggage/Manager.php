<?php

namespace App\Livewire\Baggage;

use App\Models\Flight;
use App\Models\Baggage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Manager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $flight = null;
    public $showForm = false;
    public $editingBaggage = null;
    public $search = '';
    public $selectedPassenger = null;

    public $form = [
        'passenger_id' => '',
        'tag_number' => '',
        'weight' => '',
        'container_id' => null,
        'status' => 'pending',
        'notes' => ''
    ];

    protected $rules = [
        'form.passenger_id' => 'required|exists:passengers,id',
        'form.tag_number' => 'required|string|max:255',
        'form.weight' => 'required|numeric|min:0',
        'form.container_id' => 'nullable|exists:containers,id',
        'form.status' => 'required|in:pending,loaded,offloaded',
        'form.notes' => 'nullable|string'
    ];

    public function mount(?Flight $flight = null)
    {
        $this->flight = $flight;
    }

    public function edit(Baggage $baggage)
    {
        $this->editingBaggage = $baggage;
        $this->form = $baggage->only([
            'passenger_id',
            'tag_number',
            'weight',
            'container_id',
            'status',
            'notes'
        ]);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = array_merge($this->form, [
            'flight_id' => $this->flight?->id
        ]);

        if ($this->editingBaggage) {
            $this->editingBaggage->update($data);
        } else {
            Baggage::create($data);
        }

        $this->reset('form', 'editingBaggage', 'showForm');
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Baggage saved successfully.'
        );
        $this->dispatch('baggage-saved');
    }

    public function delete(Baggage $baggage)
    {
        $baggage->delete();
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Baggage removed successfully.'
        );
    }

    public function updateContainer($baggageId, $containerId)
    {
        $baggage = Baggage::findOrFail($baggageId);
        $baggage->update([
            'container_id' => $containerId ? $containerId : null,
            'status' => $containerId ? 'loaded' : 'offloaded'
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: $containerId ? 'Baggage loaded to container.' : 'Baggage removed from container.'
        );
    }

    #[On('baggage-saved')]
    public function render()
    {
        $query = Baggage::query()->with(['passenger', 'container', 'flight']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('tag_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('passenger', function ($q) {
                        $q->whereAny(['name', 'ticket_number'], 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->flight) {
            $query->whereHas('flight', function ($q) {
                $q->where('flight_id', $this->flight->id);
            });
        }

        $query->orderByDesc('created_at');

        return view('livewire.flights.baggage.manager', [
            'baggage' => $query->paginate(10),
            'passengers' => $this->flight ? $this->flight->passengers()->get() : collect(),
            'containers' => $this->flight ? $this->flight->containers()->get() : collect()
        ]);
    }
}