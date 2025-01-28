<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Aircraft Details</h2>
            <div>
                <a href="{{ route('aircraft.flights', $aircraft) }}" class="btn btn-sm btn-info">
                    <i class="bi bi-airplane"></i> View Flights
                </a>
                <button wire:click="toggleStatus" class="btn btn-sm btn-{{ $aircraft->active ? 'success' : 'warning' }}">
                    <i class="bi bi-{{ $aircraft->active ? 'check' : 'x' }}"></i>
                    {{ ucfirst($aircraft->active ? 'Active' : 'Inactive') }}
                </button>
                <a wire:navigate href="{{ route('aircraft.edit', $aircraft) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a wire:navigate href="{{ route('aircraft.index') }}" class="btn btn-sm btn-secondary">
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
                                    <th class="w-25">Registration</th>
                                    <td>{{ $aircraft->registration }}</td>
                                </tr>
                                <tr>
                                    <th>Airline</th>
                                    <td>{{ $aircraft->airline->name }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $aircraft->type }}</td>
                                </tr>
                                <tr>
                                    <th>Model</th>
                                    <td>{{ $aircraft->model }}</td>
                                </tr>
                                <tr>
                                    <th>Passenger Capacity</th>
                                    <td>{{ $aircraft->passenger_capacity }} seats</td>
                                </tr>
                                <tr>
                                    <th>Cargo Capacity</th>
                                    <td>{{ number_format($aircraft->cargo_capacity) }} kg</td>
                                </tr>
                                <tr>
                                    <th>Empty Weight</th>
                                    <td>{{ number_format($aircraft->empty_weight) }} kg</td>
                                </tr>
                                <tr>
                                    <th>Max Takeoff Weight</th>
                                    <td>{{ number_format($aircraft->max_takeoff_weight) }} kg</td>
                                </tr>
                                <tr>
                                    <th>Max Fuel Capacity</th>
                                    <td>{{ number_format($aircraft->max_fuel_capacity) }} L</td>
                                </tr>
                                @if ($aircraft->notes)
                                    <tr>
                                        <th>Notes</th>
                                        <td>{{ $aircraft->notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title m-0">Recent Flights</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Flight</th>
                                            <th>Route</th>
                                            <th>Schedule</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($aircraft->flights as $flight)
                                            <tr wire:key="flight-{{ $flight->id }}">
                                                <td>
                                                    <a wire:navigate href="{{ route('flights.show', $flight) }}"
                                                        class="text-decoration-none">
                                                        {{ $flight->flight_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                                                <td>{{ $flight->scheduled_departure_time->format('d M Y H:i') }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }}">
                                                        {{ ucfirst($flight->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No flights found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">Quick Links</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group">
                                <a href="{{ route('aircraft.flights', $aircraft) }}"
                                    class="list-group-item list-group-item-action">
                                    Flight History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
