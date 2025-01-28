<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Container Details</h2>
            <div>
                <div class="dropdown d-inline">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        {{ ucwords(str_replace('_', ' ', $container->status)) }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><button wire:click="updateStatus('empty')" class="dropdown-item">Empty</button></li>
                        <li><button wire:click="updateStatus('loading')" class="dropdown-item">Loading</button></li>
                        <li><button wire:click="updateStatus('loaded')" class="dropdown-item">Loaded</button></li>
                        <li><button wire:click="updateStatus('unloading')" class="dropdown-item">Unloading</button></li>
                        <li><button wire:click="updateStatus('unloaded')" class="dropdown-item">Unloaded</button></li>
                    </ul>
                </div>
                <a wire:navigate href="{{ route('containers.edit', $container) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a wire:navigate href="{{ route('containers.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th class="w-25">Container Number</th>
                                    <td>{{ $container->container_number }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ ucfirst($container->type) }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-{{ $container->status === 'loaded' ? 'success' : 'warning' }}">
                                            {{ ucfirst($container->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Compartment</th>
                                    <td>
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                {{ $container->compartment ?: 'Not Assigned' }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button wire:click="updateCompartment('forward')" class="dropdown-item">Forward</button>
                                                </li>
                                                <li>
                                                    <button wire:click="updateCompartment('aft')" class="dropdown-item">Aft</button>
                                                </li>
                                                <li>
                                                    <button wire:click="updateCompartment('bulk')" class="dropdown-item">Bulk</button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button wire:click="updateCompartment('offload')"
                                                        class="dropdown-item text-danger">Offload
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Weight</th>
                                    <td>{{ number_format($container->total_weight) }} / {{ number_format($container->max_weight) }} kg</td>
                                </tr>
                                @if ($container->notes)
                                    <tr>
                                        <th>Notes</th>
                                        <td>{{ $container->notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">Flight Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th class="w-25">Flight</th>
                                    <td>
                                        <a wire:navigate href="{{ route('flights.show', $container->flight) }}"
                                            class="text-decoration-none">
                                            {{ $container->flight->flight_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Route</th>
                                    <td>{{ $container->flight->departure_airport }} → {{ $container->flight->arrival_airport }}</td>
                                </tr>
                                <tr>
                                    <th>Schedule</th>
                                    <td>{{ $container->flight->scheduled_departure_time->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Aircraft</th>
                                    <td>
                                        <a wire:navigate href="{{ route('aircraft.show', $container->flight->aircraft) }}"
                                            class="text-decoration-none">
                                            {{ $container->flight->aircraft->registration_number }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">{{ ucfirst($container->type) }} Items</h5>
                        </div>
                        <div class="card-body">
                            @if ($container->type === 'baggage')
                                @include('livewire.container.partials.baggage-list')
                            @else
                                @include('livewire.container.partials.cargo-list')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
