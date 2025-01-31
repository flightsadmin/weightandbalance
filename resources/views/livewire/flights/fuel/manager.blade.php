<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="card-title">Fuel Record Details</h2>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Flight Information</h4>
                @if ($fuel)
                    <p class="mb-1">
                        <strong>Flight Number:</strong>
                        <a wire:navigate href="{{ route('flights.show', $fuel->flight) }}" class="text-decoration-none">
                            {{ $fuel->flight->flight_number }}
                        </a>
                    </p>
                    <p class="mb-1">
                        <strong>Route:</strong>
                        {{ $fuel->flight->departure_airport }} - {{ $fuel->flight->arrival_airport }}
                    </p>
                    <p class="mb-1">
                        <strong>Scheduled Departure:</strong>
                        {{ $fuel->flight->scheduled_departure_time->format('D, M d, Y H:i') }}
                    </p>
                @else
                    <p>No flight information available</p>
                @endif
            </div>
            @if ($fuel)
                <div class="col-md-6">
                    <h4>Total Fuel</h4>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="display-6 text-primary mb-2">
                            {{ number_format($fuel->block_fuel) }} kg
                        </div>
                        <span class="badge bg-warning text-muted text-sm">
                            Max Capacity: {{ number_format($fuel->flight->aircraft->type->max_fuel_capacity) }} kg
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: {{ ($fuel->block_fuel / $fuel->flight->aircraft->type->max_fuel_capacity ?? 0) * 100 }}%">
                            {{ number_format(($fuel->block_fuel / $fuel->flight->aircraft->type->max_fuel_capacity ?? 0) * 100, 1) }}%
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0">Primary Fuel</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Block Fuel:</span>
                                <strong>{{ number_format($fuel->block_fuel) }} kg</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Take Off Fuel:</span>
                                <strong>{{ number_format($fuel->take_off_fuel) }} kg</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Taxi Fuel:</span>
                                <strong>{{ number_format($fuel->taxi_fuel) }} kg</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Trip Fuel:</span>
                                <strong>{{ number_format($fuel->trip_fuel) }} kg</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0">Crew & Pantry</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Crew:</span>
                                <strong>{{ $fuel->crew }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Pantry:</span>
                                <strong>{{ $fuel->pantry }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
