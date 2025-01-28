<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Cargo</h2>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search cargo...">

                <select wire:model.live="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="general">General</option>
                    <option value="perishable">Perishable</option>
                    <option value="dangerous_goods">Dangerous Goods</option>
                    <option value="live_animals">Live Animals</option>
                    <option value="valuable">Valuable</option>
                    <option value="mail">Mail</option>
                </select>

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="accepted">Accepted</option>
                    <option value="loaded">Loaded</option>
                    <option value="offloaded">Offloaded</option>
                </select>

                <select wire:model.live="container_id" class="form-select form-select-sm">
                    <option value="">All Containers</option>
                    @foreach ($containers as $container)
                        <option value="{{ $container->id }}">{{ $container->container_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <a wire:navigate href="{{ route('flights.show', $flight) }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Flight
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>AWB Number</th>
                            <th>Flight</th>
                            <th>Type</th>
                            <th>Weight</th>
                            <th>Container</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cargo as $item)
                            <tr wire:key="cargo-{{ $item->id }}">
                                <td>
                                    {{ $item->awb_number }}
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('flights.show', $item->flight) }}"
                                        class="text-decoration-none">
                                        {{ $item->flight->flight_number }}
                                    </a>
                                    <span class="small text-muted">
                                        ({{ $item->flight->departure_airport }} → {{ $item->flight->arrival_airport }})
                                    </span>
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $item->type)) }}</td>
                                <td>{{ number_format($item->weight) }} kg</td>
                                <td>
                                    @if ($item->container)
                                        <a href="{{ route('containers.show', $item->container) }}" class="text-decoration-none">
                                            {{ $item->container->container_number }}
                                        </a>
                                        <span class="small text-muted">
                                            ({{ number_format($item->container->total_weight) }}/{{ number_format($item->container->max_weight) }}
                                            kg)
                                        </span>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $item->status === 'loaded' ? 'success' : ($item->status === 'offloaded' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No cargo found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $cargo->links() }}
            </div>
        </div>
    </div>
</div>
