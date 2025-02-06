<?php

namespace App\Livewire\Container;

use App\Models\Container;
use App\Models\Flight;
use Livewire\Component;

class Manager extends Component
{
    public Flight $flight;

    public $editingContainer = null;

    public $showForm = false;

    public $form = [
        'container_number' => '',
        'type' => 'baggage',
        'tare_weight' => 0,
        'weight' => 0,
        'max_weight' => 0,
    ];

    protected $rules = [
        'form.container_number' => 'required|string|max:255',
        'form.type' => 'required|in:baggage,cargo',
        'form.tare_weight' => 'required|integer|min:0',
        'form.weight' => 'required|integer|min:0',
        'form.max_weight' => 'required|integer|min:0',
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function editContainer(Container $container)
    {
        $this->editingContainer = $container;
        $this->form = $container->only([
            'container_number',
            'type',
            'tare_weight',
            'weight',
            'max_weight',
        ]);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        Container::updateOrCreate(
            [
                'container_number' => $this->form['container_number'],
            ],
            [
                'flight_id' => $this->flight->id,
                'type' => $this->form['type'],
                'tare_weight' => $this->form['tare_weight'],
                'weight' => $this->form['weight'],
                'max_weight' => $this->form['max_weight'],
            ]
        );

        $this->reset('form', 'editingContainer', 'showForm');
        $this->dispatch('containerSaved');
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Container saved successfully.'
        );
    }

    public function render()
    {
        return view('livewire.flights.container.manager');
    }
}
