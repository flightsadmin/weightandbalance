<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title m-0">{{ $aircraft->registration_number }} Flights</h2>
                <p class="text-muted small m-0">{{ $aircraft->airline->name }} - {{ $aircraft->type }} {{ $aircraft->model }}</p>
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
            <a wire:navigate href="{{ route('aircraft.show', $aircraft) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Aircraft
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Flight Number</th>
                            <th>Route</th>
                            <th>STD</th>
                            <th>STA</th>
                            <th>Load</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($flights as $flight)
                            <tr wire:key="flight-{{ $flight->id }}">
                                <td>{{ $flight->flight_number }}</td>
                                <td>
                                    {{ $flight->departure_airport }} → {{ $flight->arrival_airport }}
                                </td>
                                <td>
                                    {{ $flight->scheduled_departure_time->format('d M Y H:i') }}
                                </td>
                                <td>
                                    {{ $flight->scheduled_arrival_time->format('d M Y H:i') }}
                                </td>
                                <td>
                                    @if ($flight->weightBalance)
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $percentage =
                                                    ($flight->weightBalance->takeoff_weight / $aircraft->max_takeoff_weight) * 100;
                                                $colorClass =
                                                    $percentage > 90 ? 'bg-danger' : ($percentage > 75 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $colorClass }}" role="progressbar"
                                                style="width: {{ $percentage }}%"
                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                                {{ number_format($percentage) }}%
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Not calculated</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }}">
                                        {{ ucwords($flight->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('flights.show', $flight) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No flights found</td>
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
