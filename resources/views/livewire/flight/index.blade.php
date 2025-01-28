<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Flights</h2>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search flights...">

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="boarding">Boarding</option>
                    <option value="departed">Departed</option>
                    <option value="arrived">Arrived</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <a wire:navigate href="{{ route('flights.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Add Flight
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Flight Number</th>
                            <th>Airline</th>
                            <th>Route</th>
                            <th>STA</th>
                            <th>STD</th>
                            <th>Aircraft</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($flights as $flight)
                            <tr>
                                <td>
                                    <a wire:navigate href="{{ route('flights.show', $flight) }}" class="text-decoration-none">
                                        {{ $flight->flight_number }}
                                    </a>
                                </td>
                                <td>{{ $flight->airline->iata_code }}</td>
                                <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                                <td>
                                    {{ $flight->scheduled_departure_time->format('d M Y H:i') }}

                                </td>
                                <td>
                                    {{ $flight->scheduled_arrival_time->format('d M Y H:i') }}
                                </td>
                                <td>
                                    {{ $flight->aircraft->registration_number }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-sm btn-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }} dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            {{ ucfirst($flight->status) }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'scheduled')"
                                                    class="dropdown-item">Scheduled</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'boarding')"
                                                    class="dropdown-item">Boarding</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'departed')"
                                                    class="dropdown-item">Departed</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'arrived')"
                                                    class="dropdown-item">Arrived</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'cancelled')"
                                                    class="dropdown-item">Cancelled</button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('flights.edit', $flight) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No flights found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $flights->links() }}
            </div>
        </div>
    </div>
</div>
