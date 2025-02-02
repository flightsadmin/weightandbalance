<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Airline Details</h2>
            <div>
                <a href="{{ route('airlines.settings', $airline) }}" class="btn btn-sm btn-info">
                    <i class="bi bi-gear"></i> Settings
                </a>
                <button wire:click="toggleStatus" class="btn btn-sm btn-{{ $airline->active ? 'success' : 'danger' }}">
                    <i class="bi bi-{{ $airline->active ? 'check' : 'x' }}"></i>
                    {{ $airline->active ? 'Active' : 'Inactive' }}
                </button>
                <a wire:navigate href="{{ route('airlines.edit', $airline) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a wire:navigate href="{{ route('airlines.index') }}" class="btn btn-sm btn-secondary">
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
                                    <th class="w-25">Name</th>
                                    <td>{{ $airline->name }}</td>
                                </tr>
                                <tr>
                                    <th>IATA Code</th>
                                    <td>{{ $airline->iata_code }}</td>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td>{{ $airline->country }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $airline->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $airline->email }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $airline->address }}</td>
                                </tr>
                                @if ($airline->description)
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ $airline->description }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title m-0">Aircraft</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Registration</th>
                                            <th>Type</th>
                                            <th>Model</th>
                                            <th>Capacity</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($airline->aircraft as $aircraft)
                                            <tr wire:key="aircraft-{{ $aircraft->id }}">
                                                <td>
                                                    {{ $aircraft->registration_number }}
                                                </td>
                                                <td>{{ $aircraft->type->code }} </td>
                                                <td>{{ $aircraft->type->name }}</td>
                                                <td>{{ $aircraft->type->max_passengers }} pax </td>
                                                <td>
                                                    <span class="badge bg-{{ $aircraft->active ? 'success' : 'warning' }}">
                                                        {{ ucfirst($aircraft->active ? 'Active' : 'Inactive') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No aircraft found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
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
                                        @forelse($airline->flights as $flight)
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
                </div>
            </div>
        </div>
    </div>
</div>
