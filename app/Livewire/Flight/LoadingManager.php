<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LoadingManager extends Component
{
    public Flight $flight;

    public $loadplan;

    public $holds;

    public $containers;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load([
            'aircraft.type.holds.positions',
            'containers' => fn ($q) => $q->withPivot(['type', 'pieces', 'weight', 'status', 'position_id']),
        ]);

        $this->loadplan = $flight->loadplans()->latest()->first();

        $this->holds = $this->flight->aircraft->type->holds->map(function ($hold) {
            return [
                'id' => $hold->id,
                'name' => $hold->name,
                'max_weight' => $hold->max_weight,
                'positions' => $hold->positions->map(fn ($pos) => [
                    'id' => $pos->id,
                    'designation' => $pos->code,
                ])->toArray(),
            ];
        })->toArray();

        $this->containers = $this->flight->containers->map(function ($container) {
            return [
                'id' => $container->id,
                'uld_code' => $container->container_number,
                'type' => $container->pivot->type,
                'weight' => $container->pivot->weight,
                'pieces' => $container->pivot->pieces,
                'position' => $container->pivot->position_id,
                'position_code' => $container->pivot->position_id,
                'status' => $container->pivot->status,
                'destination' => $this->flight->arrival_airport,
                'updated_at' => now()->toDateTimeString(),
            ];
        })->toArray();
    }

    public function saveLoadplan($containers)
    {
        try {
            DB::beginTransaction();

            if ($this->loadplan) {
                $this->loadplan->update([
                    'loading' => [
                        'containers' => $containers,
                        'holds' => $this->holds,
                    ],
                    'last_modified_by' => auth()->id(),
                ]);
            } else {
                // Create new loadplan if none exists
                $this->loadplan = $this->flight->loadplans()->create([
                    'loading' => [
                        'containers' => $containers,
                        'holds' => $this->holds,
                    ],
                    'last_modified_by' => auth()->id(),
                ]);
            }

            // Update container positions
            foreach ($containers as $container) {
                $this->flight->containers()->updateExistingPivot($container['id'], [
                    'position_id' => $container['position'] ? $container['position'][0] : null,
                    'status' => $container['position'] ? 'loaded' : 'unloaded',
                ]);
            }

            DB::commit();
            $this->dispatch('alert', icon: 'success', message: 'Load plan saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to save load plan');
            \Log::error('Failed to save loadplan: '.$e->getMessage());
        }
    }

    public function resetLoadplan()
    {
        try {
            DB::beginTransaction();

            foreach ($this->flight->containers as $container) {
                $container->pivot->update([
                    'position_id' => null,
                    'status' => 'unloaded',
                ]);
            }

            if ($this->loadplan) {
                $this->loadplan->update([
                    'loading' => null,
                ]);
            }

            DB::commit();
            $this->dispatch('resetAlpineState');
            $this->dispatch('alert', icon: 'success', message: 'Load plan reset successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to reset load plan');
            \Log::error('Failed to reset loadplan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.flights.loading-manager');
    }
}
