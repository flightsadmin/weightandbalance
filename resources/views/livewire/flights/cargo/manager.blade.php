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
            @if ($cargo->count() > 0)
                <div class="mb-3 d-flex gap-2 align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" wire:model.live="selectAll" id="selectAll">
                        <label class="form-check-label" for="selectAll">Select All</label>
                    </div>

                    @if (!empty($selected))
                        <select class="form-select form-select-sm w-auto" wire:model="bulkContainer">
                            <option value="">Select Container</option>
                            @foreach ($containers as $container)
                                <option value="{{ $container->id }}">{{ $container->container_number }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-primary btn-sm" wire:click="loadSelectedToContainer">
                            Load Selected ({{ count($selected) }})
                        </button>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th><input type="checkbox" wire:model.live="selectAll"></th>
                                <th>AWB Number</th>
                                <th>Flight</th>
                                <th>Type</th>
                                <th>Pieces</th>
                                <th>Weight</th>
                                <th>Container</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cargo as $item)
                                <tr wire:key="cargo-{{ $item->id }}">
                                    <td>
                                        <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}">
                                    </td>
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
                                    <td>{{ $item->pieces }}</td>
                                    <td>{{ number_format($item->weight) }} kg</td>
                                    <td>
                                        <div class="dropdown d-inline">
                                            <button
                                                class="btn btn-sm btn-{{ $item->container_id ? 'success' : 'danger' }} dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                {{ $item->container_id ? $item->container->container_number : 'Not loaded' }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach ($containers as $container)
                                                    <li>
                                                        <button class="dropdown-item"
                                                            wire:click="updateContainer({{ $item->id }}, {{ $container->id }})">
                                                            <i class="bi bi-check-circle text-success"></i>
                                                            {{ $container->container_number }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateContainer({{ $item->id }}, null)">
                                                        <i class="bi bi-x-circle text-danger"></i> Offloaded
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        @if ($item->container)
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
            @endif

            <div class="mt-4">
                {{ $cargo->links() }}
            </div>
        </div>
    </div>
</div>
