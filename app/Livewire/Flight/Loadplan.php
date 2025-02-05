<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Attributes\On;
use Livewire\Component;

class Loadplan extends Component
{
    public $flight;

    public $loadplan;

    public $containerPositions = [];

    public $isDragging = false;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load('aircraft.type.holds.positions');
        $this->loadplan = $flight->loadplans()->latest()->first()
            ?? $flight->loadplans()->create([
                'status' => 'draft',
                'version' => 0,
                'last_modified_by' => auth()->id(),
            ]);

        $this->containerPositions = $this->loadplan->container_positions ?? [];
    }

    public function updateContainerPosition($containerId, $fromPosition, $toPosition)
    {
        // Get the container
        $container = $this->flight->containers()->find($containerId);

        // Get the hold type (FH, AH, BH) for the position
        $holdType = null;
        $compartment = null;
        if ($toPosition) {
            $position = $this->flight->aircraft->type->holds()
                ->whereHas('positions', function ($query) use ($toPosition) {
                    $query->where('id', $toPosition);
                })->first();
            $holdType = $position?->code;
            $compartment = $toPosition;
        }

        // Find current position if fromPosition is null
        if (! $fromPosition && isset($this->containerPositions[$containerId])) {
            $fromPosition = $this->containerPositions[$containerId];
        }

        // Remove from old position
        if (isset($this->containerPositions[$containerId])) {
            unset($this->containerPositions[$containerId]);
        }

        // Add to new position and update container status
        if ($toPosition) {
            $this->containerPositions[$containerId] = $toPosition;
            $container->update([
                'status' => 'loaded',
                'compartment' => $compartment,
            ]);
        } else {
            // If dropped back to unplanned, reset status and compartment
            $container->update([
                'status' => 'unloaded',
                'compartment' => null,
            ]);
        }

        // Save changes to loadplan
        $this->loadplan->update([
            'container_positions' => $this->containerPositions,
            'last_modified_by' => auth()->id(),
        ]);

        // Force a refresh of the component
        $this->dispatch('containerMoved');
    }

    public function releaseLoadplan()
    {
        // Validate total weight per hold
        $overweightHolds = $this->flight->aircraft->type->holds
            ->filter(fn ($hold) => $hold->isOverweight(
                $hold->getCurrentWeight($this->containerPositions, $this->flight->containers)
            ));

        if ($overweightHolds->isNotEmpty()) {
            foreach ($overweightHolds as $hold) {
                $this->dispatch(
                    'alert',
                    icon: 'error',
                    message: "{$hold->name} is over its maximum weight limit."
                );
            }

            return;
        }

        $this->loadplan->update([
            'status' => 'released',
            'released_by' => auth()->id(),
            'released_at' => now(),
            'version' => $this->loadplan->increment('version'),
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Loadplan released successfully.'
        );
    }

    #[On('containerSaved')]
    public function render()
    {
        $containers = $this->flight->containers()->get();
        $availableContainers = $containers->whereNotIn('id', array_keys($this->containerPositions));
        $holds = $this->flight->aircraft->type->holds()
            ->with('positions')
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return view('livewire.flight.loadplan', [
            'containers' => $containers,
            'availableContainers' => $availableContainers,
            'holds' => $holds,
            'aircraft' => $this->flight->aircraft,
        ]);
    }
}
