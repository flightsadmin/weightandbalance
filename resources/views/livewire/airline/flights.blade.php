<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title m-0">{{ $airline->name }} Flights</h2>
                <p class="text-muted small m-0">Manage airline flight schedules</p>
            </div>
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
            <div>
                <a wire:navigate href="{{ route('flights.create', ['airline_id' => $airline->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Flight
                </a>
                <a wire:navigate href="{{ route('airlines.show', $airline) }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Airline
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Flight Number</th>
                            <th>Route</th>
                            <th>Aircraft</th>
                            <th>Schedule</th>
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
                                <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                                <td>
                                    <a wire:navigate href="{{ route('aircraft.show', $flight->aircraft) }}">
                                        {{ $flight->aircraft->registration_number }}
                                    </a>
                                </td>
                                <td>{{ $flight->scheduled_departure_time->format('d M Y H:i') }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }}">
                                        {{ ucfirst($flight->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('flights.edit', $flight) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
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
