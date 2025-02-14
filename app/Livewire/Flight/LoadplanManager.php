<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Attributes\On;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class LoadplanManager extends Component
{
    public $flight;

    public $loadplan;

    public $containerPositions = [];

    public $loadingInstructions = [];

    public $isDragging = false;

    public $showLirfPreview = false;

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
        $container = $this->flight->containers()->find($containerId);
        if (!$container)
            return;

        $position = null;
        if ($toPosition) {
            $position = $this->flight->aircraft->type->holds()
                ->whereHas('positions', function ($query) use ($toPosition) {
                    $query->where('id', $toPosition);
                })
                ->with([
                    'positions' => function ($query) use ($toPosition) {
                        $query->where('id', $toPosition);
                    },
                ])
                ->first()?->positions->first();
        }

        if (!$fromPosition && isset($this->containerPositions[$containerId])) {
            $fromPosition = $this->containerPositions[$containerId];
        }

        if (isset($this->containerPositions[$containerId])) {
            unset($this->containerPositions[$containerId]);
        }

        if ($position) {
            $this->containerPositions[$containerId] = $position->id;
            $container->updatePosition($position->id, $this->flight->id);
            $this->dispatch('container_position_updated');
        } else {
            $container->updatePosition(null, $this->flight->id);
            $this->dispatch('container_position_updated');
        }

        $this->loadplan->update([
            'container_positions' => $this->containerPositions,
            'last_modified_by' => auth()->id(),
        ]);

        $this->dispatch('containerMoved');
    }

    public function releaseLoadplan()
    {
        $overweightHolds = $this->flight->aircraft->type->holds
            ->filter(fn($hold) => $hold->isOverweight(
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

    public function printLIRF()
    {
        if ($this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Loadplan must be released before printing LIRF.');
            return;
        }

        $loadInstructions = collect($this->containerPositions)->map(function ($positionId, $containerId) {
            $container = $this->flight->containers->find($containerId);
            $position = $this->flight->aircraft->type->holds()
                ->whereHas('positions', fn($q) => $q->where('id', $positionId))
                ->with(['positions' => fn($q) => $q->where('id', $positionId)])
                ->first()
                ->positions
                ->first();

            return [
                'hold' => $position->hold->name,
                'position' => $position->code,
                'container_number' => $container->container_number,
                'content_type' => $container->pivot->type,
                'weight' => $container->weight,
                'destination' => $this->flight->arrival_airport,
            ];
        })->sortBy([
                    ['hold', 'asc'],
                    ['position', 'asc'],
                ])->values();

        $this->loadingInstructions = $loadInstructions;
        $holdSummary = $this->flight->aircraft->type->holds
            ->map(function ($hold) {
                $actualWeight = $hold->getCurrentWeight($this->containerPositions, $this->flight->containers);
                return [
                    'name' => $hold->name,
                    'actual_weight' => $actualWeight,
                    'max_weight' => $hold->max_weight,
                    'available' => $hold->max_weight - $actualWeight,
                ];
            });

        $pdf = Pdf::loadView('livewire.flights.loading-instruction', [
            'flight' => $this->flight,
            'loadplan' => $this->loadplan,
            'loadInstructions' => $this->loadingInstructions,
            'holdSummary' => $holdSummary,
        ]);

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            "LIRF_{$this->flight->flight_number}_{$this->loadplan->version}.pdf"
        );
    }

    public function previewLIRF()
    {
        if ($this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Loadplan must be released before printing LIRF.');
            return;
        }

        $loadInstructions = collect($this->containerPositions)->map(function ($positionId, $containerId) {
            $container = $this->flight->containers->find($containerId);
            $position = $this->flight->aircraft->type->holds()
                ->whereHas('positions', fn($q) => $q->where('id', $positionId))
                ->with(['positions' => fn($q) => $q->where('id', $positionId)])
                ->first()
                ->positions
                ->first();

            return [
                'hold' => $position->hold->name,
                'position' => $position->code,
                'container_number' => $container->container_number,
                'content_type' => $container->pivot->type,
                'weight' => $container->weight,
                'destination' => $this->flight->arrival_airport,
            ];
        })->sortBy([
                    ['hold', 'asc'],
                    ['position', 'asc'],
                ])->values();

        $this->loadingInstructions = $loadInstructions;

        $holdSummary = $this->flight->aircraft->type->holds
            ->map(function ($hold) {
                $actualWeight = $hold->getCurrentWeight($this->containerPositions, $this->flight->containers);
                return [
                    'name' => $hold->name,
                    'actual_weight' => $actualWeight,
                    'max_weight' => $hold->max_weight,
                    'available' => $hold->max_weight - $actualWeight,
                ];
            });

        $this->showLirfPreview = true;
        $this->dispatch('show-lirf-preview', [
            'flight' => $this->flight,
            'loadplan' => $this->loadplan,
            'loadInstructions' => $loadInstructions,
            'holdSummary' => $holdSummary,
        ]);
    }

    #[On('containerSaved')]
    public function render()
    {
        $containers = $this->flight->containers()
            ->withPivot(['type', 'status', 'position_id'])
            ->get();

        $availableContainers = $containers->whereNotIn('id', array_keys($this->containerPositions));
        $holds = $this->flight->aircraft->type->holds()
            ->with('positions')
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return view('livewire.flights.loadplan-manager', [
            'containers' => $containers,
            'availableContainers' => $availableContainers,
            'holds' => $holds,
            'aircraft' => $this->flight->aircraft,
        ]);
    }
}
