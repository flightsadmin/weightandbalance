<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title m-0">Containers</h2>
                <p class="text-muted small m-0">Manage flight containers and loading</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search containers...">

                <select wire:model.live="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="baggage">Baggage</option>
                    <option value="cargo">Cargo</option>
                </select>

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="empty">Empty</option>
                    <option value="loading">Loading</option>
                    <option value="loaded">Loaded</option>
                    <option value="unloading">Unloading</option>
                    <option value="unloaded">Unloaded</option>
                </select>
            </div>
            <a wire:navigate href="{{ route('containers.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Add Container
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Container</th>
                            <th>Flight</th>
                            <th>Type</th>
                            <th>Compartment</th>
                            <th>Status</th>
                            <th>Tare Weight</th>
                            <th>Weight</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($containers as $container)
                            <tr>
                                <td>
                                    <a wire:navigate href="{{ route('containers.show', $container) }}" class="text-decoration-none">
                                        {{ $container->container_number }}
                                    </a>
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('flights.show', $container->flight) }}"
                                        class="text-decoration-none">
                                        {{ $container->flight->flight_number }}
                                    </a>
                                </td>
                                <td>{{ ucfirst($container->type) }}</td>
                                <td>{{ ucfirst($container->compartment) }}</td>
                                <td>
                                    <span class="badge bg-{{ $container->status === 'loaded' ? 'success' : 'warning' }}">
                                        {{ ucfirst($container->status) }}
                                    </span>
                                </td>
                                <td>{{ number_format($container->tare_weight) }} kg</td>
                                <td>{{ number_format($container->weight) }} / {{ number_format($container->max_weight) }} kg</td>
                                <td>
                                    @if ($container->type === 'baggage')
                                        {{ $container->baggage->count() }} pcs
                                    @else
                                        {{ $container->cargo->count() }} pcs
                                    @endif
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('containers.edit', $container) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No containers found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $containers->links() }}
            </div>
        </div>
    </div>
</div>
