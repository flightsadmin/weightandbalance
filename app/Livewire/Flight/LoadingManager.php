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

    public $showLirfPreview = false;

    public $loadInstructions = [];

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
                    'released_by' => auth()->id(),
                    'released_at' => now(),
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'released',
                    'version' => $this->loadplan->version + 1,
                ]);
            } else {
                $this->loadplan = $this->flight->loadplans()->create([
                    'loading' => $loadingData,
                    'released_by' => auth()->id(),
                    'released_at' => now(),
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'draft',
                    'version' => 1,
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
            ->limit(10)
            ->get()
            ->map(function ($container) {
                $isAttached = $this->flight->containers->contains('id', $container->id);
                return [
                    'id' => $container->id,
                    'container_number' => $container->container_number,
                    'is_attached' => $isAttached,
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
            $this->dispatch('container_position_updated');

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

    public function detachContainer($containerId)
    {
        try {
            DB::beginTransaction();

            $container = Container::findOrFail($containerId);

            // First, empty the container by resetting its pivot data
            $this->flight->containers()->updateExistingPivot($containerId, [
                'weight' => $container->tare_weight,
                'pieces' => 0,
                'position_id' => null,
                'status' => 'unloaded'
            ]);

            $this->containers = collect($this->containers)
                ->filter(function ($container) use ($containerId) {
                    return $container['id'] !== $containerId;
                })->toArray();

            $this->flight->containers()->detach($containerId);

            if ($this->loadplan) {
                $this->loadplan->update([
                    'loading' => collect($this->loadplan->loading ?? [])
                        ->filter(function ($item, $key) use ($containerId) {
                            return $key != $containerId;
                        })
                        ->toArray(),
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now(),
                ]);
            }

            DB::commit();

            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: 'Container detached successfully');

            return [
                'success' => true,
                'message' => 'Container detached and contents unloaded successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to detach container: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to detach container'
            ];
        }
    }

    public function previewLIRF()
    {
        if (isset($this->loadplan) && $this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Loadplan must be released before printing LIRF.');
            return;
        }

        $this->loadInstructions = collect($this->flight->aircraft->type->holds()
            ->with('positions')
            ->get()
            ->flatMap(function ($hold) {
                return $hold->positions->map(function ($position) use ($hold) {
                    $containerData = collect($this->containers)
                        ->first(function ($container) use ($position) {
                            return $container['position'] === $position->id;
                        });

                    return [
                        'hold' => $hold->name,
                        'position' => $position->code,
                        'container_number' => $containerData['uld_code'] ?? 'NIL',
                        'content_type' => $containerData['type'] ?? 'NIL',
                        'weight' => $containerData['weight'] ?? 0,
                        'pieces' => $containerData['pieces'] ?? null,
                        'destination' => $containerData['destination'] ?? $this->flight->arrival_airport,
                        'is_empty' => is_null($containerData),
                    ];
                });
            }))
            ->sortBy([
                ['hold', 'asc'],
                ['position', 'asc'],
            ])->values()->toArray();

        $this->showLirfPreview = true;
        $this->dispatch('show-lirf-preview');
    }

    public function render()
    {
        return view('livewire.flights.loading-manager');
    }
}
