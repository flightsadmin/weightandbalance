<?php

namespace App\Livewire\Flight;

use App\Models\Container;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
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
            'containers' => fn($q) => $q->withPivot(['type', 'pieces', 'weight', 'status', 'position_id']),
        ]);

        $this->loadplan = $flight->loadplans()->latest()->first();

        $this->holds = $this->flight->aircraft->type->holds->map(function ($hold) {
            return [
                'id' => $hold->id,
                'name' => $hold->name,
                'max_weight' => $hold->max_weight,
                'positions' => $hold->positions->map(fn($pos) => [
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

            $formattedContainers = collect($containers)->mapWithKeys(function ($container) {
                $hold = collect($this->holds)->first(function ($hold) use ($container) {
                    return collect($hold['positions'])->pluck('id')->contains($container['position']);
                });

                return [
                    $container['id'] => [
                        'pieces' => $container['pieces'],
                        'weight' => $container['weight'],
                        'hold_name' => $hold ? $hold['name'] : null,
                        'updated_at' => now()->toDateTimeString(),
                        'destination' => $container['destination'],
                        'position_id' => $container['position'],
                        'content_type' => $container['type'],
                        'position_code' => $container['position_code'],
                        'container_number' => $container['uld_code'],
                    ]
                ];
            })->toArray();

            $loadingData = [
                'containers' => $formattedContainers,
                'holds' => collect($this->holds)->map(function ($hold) use ($containers) {
                    $holdWeight = collect($containers)
                        ->filter(function ($container) use ($hold) {
                            return collect($hold['positions'])->pluck('id')->contains($container['position']);
                        })
                        ->sum('weight');

                    return array_merge($hold, [
                        'current_weight' => $holdWeight,
                        'utilization' => ($holdWeight / $hold['max_weight']) * 100
                    ]);
                })->toArray(),
            ];

            if ($this->loadplan) {
                $this->loadplan->update([
                    'loading' => $loadingData,
                    'last_modified_by' => auth()->id(),
                ]);
            } else {
                $this->loadplan = $this->flight->loadplans()->create([
                    'loading' => $loadingData,
                    'last_modified_by' => auth()->id(),
                ]);
            }

            foreach ($containers as $container) {
                $this->flight->containers()->updateExistingPivot($container['id'], [
                    'position_id' => $container['position'],
                    'status' => $container['position'] ? 'loaded' : 'unloaded',
                ]);
            }

            DB::commit();
            $this->dispatch('alert', icon: 'success', message: 'Load plan saved successfully');
            $this->dispatch('container_position_updated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to save load plan');
            \Log::error('Failed to save loadplan: ' . $e->getMessage());
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
            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: 'Load plan reset successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to reset load plan');
            \Log::error('Failed to reset loadplan: ' . $e->getMessage());
        }
    }

    public function searchContainers($query)
    {
        if (empty($query)) {
            return [];
        }

        return Container::where('container_number', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($container) {
                return [
                    'id' => $container->id,
                    'container_number' => $container->container_number,
                ];
            });
    }

    public function attachContainer($containerId, $type = 'cargo')
    {
        try {
            DB::beginTransaction();

            $container = Container::findOrFail($containerId);

            // Check if container is already attached
            if ($this->flight->containers()->where('container_id', $containerId)->exists()) {
                return [
                    'success' => false,
                    'message' => 'Container is already attached to this flight'
                ];
            }

            $this->flight->containers()->attach($containerId, [
                'type' => $type,
                'weight' => $container->tare_weight,
                'pieces' => 0,
                'status' => 'unloaded',
            ]);

            $newContainer = [
                'id' => $container->id,
                'uld_code' => $container->container_number,
                'type' => $type,
                'weight' => $container->tare_weight,
                'pieces' => 0,
                'position' => null,
                'position_code' => null,
                'status' => 'unloaded',
                'destination' => $this->flight->arrival_airport,
                'updated_at' => now()->toDateTimeString(),
            ];

            DB::commit();

            return [
                'success' => true,
                'message' => 'Container attached successfully',
                'container' => $newContainer
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to attach container'
            ];
        }
    }

    public function render()
    {
        return view('livewire.flights.loading-manager');
    }
}
