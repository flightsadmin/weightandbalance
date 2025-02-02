<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\CrewSeating as CrewSeatingModel;
use Livewire\Component;

class CrewSeating extends Component
{
    public AircraftType $aircraftType;
    public $isEditable = false;

    public $deck_crew = [];
    public $cabin_crew = [];

    protected $rules = [
        'deck_crew.*.location' => 'required|string',
        'deck_crew.*.max_number' => 'required|numeric',
        'deck_crew.*.arm' => 'required|numeric',
        'deck_crew.*.index_per_kg' => 'required|numeric',
        'cabin_crew.*.location' => 'required|string',
        'cabin_crew.*.max_number' => 'required|numeric',
        'cabin_crew.*.arm' => 'required|numeric',
        'cabin_crew.*.index_per_kg' => 'required|numeric',
    ];

    public function mount()
    {
        $this->loadCrewSeating();
    }

    protected function loadCrewSeating()
    {
        $this->deck_crew = $this->aircraftType->crewSeating()
            ->where('position', 'deck_crew')
            ->get()
            ->map(function ($crew) {
                return [
                    'id' => $crew->id,
                    'location' => $crew->location,
                    'max_number' => $crew->max_number,
                    'arm' => $crew->arm,
                    'index_per_kg' => $crew->index_per_kg
                ];
            })
            ->toArray();

        $this->cabin_crew = $this->aircraftType->crewSeating()
            ->where('position', 'cabin_crew')
            ->get()
            ->map(function ($crew) {
                return [
                    'id' => $crew->id,
                    'location' => $crew->location,
                    'max_number' => $crew->max_number,
                    'arm' => $crew->arm,
                    'index_per_kg' => $crew->index_per_kg
                ];
            })
            ->toArray();

        if (empty($this->deck_crew)) {
            $this->deck_crew = [
                [
                    'location' => '',
                    'max_number' => '',
                    'arm' => '',
                    'index_per_kg' => ''
                ]
            ];
        }

        if (empty($this->cabin_crew)) {
            $this->cabin_crew = [
                [
                    'location' => '',
                    'max_number' => '',
                    'arm' => '',
                    'index_per_kg' => ''
                ]
            ];
        }
    }

    public function toggleEdit()
    {
        $this->isEditable = !$this->isEditable;
    }

    public function addCrew()
    {
        $this->cabin_crew[] = [
            'location' => '',
            'max_number' => '',
            'arm' => '',
            'index_per_kg' => '',
        ];
    }

    public function removeCrew($index)
    {
        if (isset($this->cabin_crew[$index])) {
            if (isset($this->cabin_crew[$index]['id'])) {
                CrewSeatingModel::destroy($this->cabin_crew[$index]['id']);
            }
            unset($this->cabin_crew[$index]);
            $this->cabin_crew = array_values($this->cabin_crew);
        }
    }

    public function save()
    {
        $this->validate();

        // Save deck crew
        foreach ($this->deck_crew as $crew) {
            $data = [
                'position' => 'deck_crew',
                'location' => $crew['location'],
                'max_number' => $crew['max_number'],
                'arm' => $crew['arm'],
                'index_per_kg' => $crew['index_per_kg']
            ];

            if (isset($crew['id'])) {
                CrewSeatingModel::find($crew['id'])->update($data);
            } else {
                $this->aircraftType->crewSeating()->create($data);
            }
        }

        // Save cabin crew
        foreach ($this->cabin_crew as $crew) {
            $data = [
                'position' => 'cabin_crew',
                'location' => $crew['location'],
                'max_number' => $crew['max_number'],
                'arm' => $crew['arm'],
                'index_per_kg' => $crew['index_per_kg']
            ];

            if (isset($crew['id'])) {
                CrewSeatingModel::find($crew['id'])->update($data);
            } else {
                $this->aircraftType->crewSeating()->create($data);
            }
        }

        $this->dispatch('refreshAvailable', $this->aircraftType->id);
        $this->dispatch('alert', icon: 'success', message: 'Crew data saved successfully.');
        $this->toggleEdit();
        $this->loadCrewSeating();
    }

    public function render()
    {
        return view('livewire.aircraft-type.crew-seating');
    }
}