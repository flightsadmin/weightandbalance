<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\Hold;
use App\Models\Setting;
use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public AircraftType $aircraftType;
    public $activeTab = 'overview';
    public $showHoldModal = false;
    public $editingHold = null;

    public $holdForm = [
        'name' => '',
        'code' => '',
        'position' => 1,
        'max_weight' => 2000,
        'is_active' => true,
        'positions' => []
    ];

    protected $rules = [
        'holdForm.name' => 'required|string|max:255',
        'holdForm.code' => 'required|string|max:10',
        'holdForm.position' => 'required|integer|min:1',
        'holdForm.max_weight' => 'required|numeric|min:0',
        'holdForm.is_active' => 'boolean',
        'holdForm.positions.*.row' => 'required|integer|min:1',
        'holdForm.positions.*.side' => 'nullable|in:L,R',
        'holdForm.positions.*.max_weight' => 'required|numeric|min:0',
        'holdForm.positions.*.is_active' => 'boolean',
    ];

    public function mount(AircraftType $aircraft_type)
    {
        $this->aircraftType = $aircraft_type;
    }

    public function render()
    {
        return view('livewire.aircraft_type.show', [
            'holds' => $this->aircraftType->holds()->with('positions')->orderBy('position')->get(),
            'aircraft' => $this->aircraftType->aircraft()
                ->with('airline')
                ->orderBy('registration_number')
                ->paginate(10),
        ]);
    }

    public function editHold(Hold $hold)
    {
        $this->editingHold = $hold;
        $this->holdForm = array_merge($hold->toArray(), [
            'positions' => $hold->positions->map(function ($position) {
                return [
                    'row' => $position->row,
                    'side' => $position->side,
                    'max_weight' => $position->max_weight,
                    'is_active' => $position->is_active
                ];
            })->toArray()
        ]);
        $this->showHoldModal = true;
    }

    public function addHoldPosition()
    {
        $lastRow = collect($this->holdForm['positions'])->pluck('row')->max() ?? 0;

        if ($this->holdForm['code'] === 'BH') {
            $this->holdForm['positions'][] = [
                'row' => $lastRow + 1,
                'side' => null,
                'max_weight' => 2000,
                'is_active' => true
            ];
        } else {
            $this->holdForm['positions'][] = [
                'row' => $lastRow + 1,
                'side' => 'L',
                'max_weight' => 2000,
                'is_active' => true
            ];
            $this->holdForm['positions'][] = [
                'row' => $lastRow + 1,
                'side' => 'R',
                'max_weight' => 2000,
                'is_active' => true
            ];
        }
    }

    public function removeHoldPosition($index)
    {
        unset($this->holdForm['positions'][$index]);
        $this->holdForm['positions'] = array_values($this->holdForm['positions']);
    }

    public function saveHold()
    {
        $this->validate();

        $hold = $this->aircraftType->holds()->updateOrCreate(
            [
                'id' => $this->holdForm['id'] ?? null,
            ],
            collect($this->holdForm)->except('positions')->toArray()
        );
        $hold->positions()->delete();
        foreach ($this->holdForm['positions'] as $position) {
            $hold->positions()->create($position);
        }

        $this->reset('holdForm', 'showHoldModal');
        $this->dispatch('hold-saved');
    }

    public function deleteHold(Hold $hold)
    {
        $hold->positions()->delete();
        $hold->delete();
    }

    public function createHold()
    {
        $this->reset('holdForm', 'editingHold');
        $this->holdForm['positions'] = [];
        $this->showHoldModal = true;
    }
}