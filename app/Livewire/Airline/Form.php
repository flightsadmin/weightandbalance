<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Component;

class Form extends Component
{
    public ?Airline $airline = null;

    public $name = '';
    public $iata_code = '';
    public $country = '';
    public $address = '';
    public $phone = '';
    public $email = '';
    public $description = '';
    public $active = true;

    public function mount(Airline $airline)
    {
        if ($airline->exists) {
            $this->airline = $airline;
            $this->name = $airline->name;
            $this->iata_code = $airline->iata_code;
            $this->country = $airline->country;
            $this->address = $airline->address;
            $this->phone = $airline->phone;
            $this->email = $airline->email;
            $this->description = $airline->description;
            $this->active = $airline->active;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'iata_code' => 'required|string|max:10|unique:airlines,iata_code,' . ($this->airline?->id ?: 'NULL'),
            'country' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        if ($this->airline) {
            $this->airline->update($validated);
            $message = 'Airline updated successfully.';
        } else {
            $this->airline = Airline::create($validated);
            $message = 'Airline created successfully.';
        }

        $this->dispatch(
            'alert',
            icon: 'success',
            message: $message
        );

        return $this->redirect(route('airlines.index'), true);
    }

    public function render()
    {
        return view('livewire.airline.form')->layout('components.layouts.app');
    }
}