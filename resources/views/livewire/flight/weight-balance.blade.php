<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Weight & Balance</h2>
            <div>
                <button class="btn btn-sm btn-success"
                    {{ !$weights['is_takeoff_weight_ok'] || !$weights['is_landing_weight_ok'] || !$weights['is_zero_fuel_weight_ok'] ? 'disabled' : '' }}>
                    <i class="bi bi-check-circle"></i> Approve Loadsheet
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Flight Info Summary -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">Flight Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th class="w-25">Flight</th>
                                    <td>{{ $flight->flight_number }}</td>
                                </tr>
                                <tr>
                                    <th>Route</th>
                                    <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                                </tr>
                                <tr>
                                    <th>Aircraft</th>
                                    <td>{{ $flight->aircraft->registration_number }}
                                        ({{ $flight->aircraft->type->name }})</td>
                                </tr>
                                <tr>
                                    <th>Crew</th>
                                    <td>{{ $flight->fuel?->crew ?? '2/4' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Weight Status Summary -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">Weight Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <div class="weight-status-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Zero Fuel Weight</span>
                                        <span class="badge {{ $weights['is_zero_fuel_weight_ok'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($weights['zero_fuel_weight']) }} /
                                            {{ number_format($weights['max_zero_fuel_weight']) }} kg
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 5px">
                                        <div class="progress-bar {{ $weights['is_zero_fuel_weight_ok'] ? 'bg-success' : 'bg-danger' }}"
                                            style="width: {{ ($weights['zero_fuel_weight'] / $weights['max_zero_fuel_weight']) * 100 }}%">
                                        </div>
                                    </div>
                                </div>

                                <div class="weight-status-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Take-off Weight</span>
                                        <span class="badge {{ $weights['is_takeoff_weight_ok'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($weights['take_off_weight']) }} /
                                            {{ number_format($weights['max_takeoff_weight']) }} kg
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 5px">
                                        <div class="progress-bar {{ $weights['is_takeoff_weight_ok'] ? 'bg-success' : 'bg-danger' }}"
                                            style="width: {{ ($weights['take_off_weight'] / $weights['max_takeoff_weight']) * 100 }}%">
                                        </div>
                                    </div>
                                </div>

                                <div class="weight-status-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Landing Weight</span>
                                        <span class="badge {{ $weights['is_landing_weight_ok'] ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($weights['landing_weight']) }} /
                                            {{ number_format($weights['max_landing_weight']) }} kg
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 5px">
                                        <div class="progress-bar {{ $weights['is_landing_weight_ok'] ? 'bg-success' : 'bg-danger' }}"
                                            style="width: {{ ($weights['landing_weight'] / $weights['max_landing_weight']) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Weight Breakdown -->
            <div class="row g-4">
                <!-- Basic Weights -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">Basic Weights</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Basic Empty Weight:</span>
                                    <strong>{{ number_format($weights['basic_empty_weight']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Operating Empty Weight:</span>
                                    <strong>{{ number_format($weights['operating_empty_weight']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Zero Fuel Weight:</span>
                                    <strong>{{ number_format($weights['zero_fuel_weight']) }} kg</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payload -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">Payload ({{ number_format($weights['total_payload']) }} kg)</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Passengers ({{ $flight->passengers->count() }}):</span>
                                    <strong>{{ number_format($weights['passenger_weight']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Baggage:</span>
                                    <strong>{{ number_format($weights['baggage_weight']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Cargo:</span>
                                    <strong>{{ number_format($weights['cargo_weight']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Container Tare:</span>
                                    <strong>{{ number_format($weights['container_tare_weight']) }} kg</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Fuel -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">Fuel</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Block Fuel:</span>
                                    <strong>{{ number_format($weights['block_fuel']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Taxi Fuel:</span>
                                    <strong>{{ number_format($weights['taxi_fuel']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Take-off Fuel:</span>
                                    <strong>{{ number_format($weights['take_off_fuel']) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Trip Fuel:</span>
                                    <strong>{{ number_format($weights['trip_fuel']) }} kg</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weight Limits Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title m-0">Weight Limits Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Weight Type</th>
                                            <th class="text-end">Actual</th>
                                            <th class="text-end">Maximum</th>
                                            <th class="text-end">Difference</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Zero Fuel Weight</td>
                                            <td class="text-end">{{ number_format($weights['zero_fuel_weight']) }} kg</td>
                                            <td class="text-end">{{ number_format($weights['max_zero_fuel_weight']) }} kg</td>
                                            <td class="text-end">{{ number_format($weights['max_zero_fuel_weight_diff']) }} kg</td>
                                            <td class="text-center">
                                                <i
                                                    class="bi bi-{{ $weights['is_zero_fuel_weight_ok'] ? 'check text-success' : 'x text-danger' }}"></i>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Take-off Weight</td>
                                            <td class="text-end">{{ number_format($weights['take_off_weight']) }} kg</td>
                                            <td class="text-end">{{ number_format($weights['max_takeoff_weight']) }} kg</td>
                                            <td class="text-end">{{ number_format($weights['max_takeoff_weight_diff']) }} kg</td>
                                            <td class="text-center">
                                                <i
                                                    class="bi bi-{{ $weights['is_takeoff_weight_ok'] ? 'check text-success' : 'x text-danger' }}"></i>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Landing Weight</td>
                                            <td class="text-end">{{ number_format($weights['landing_weight']) }} kg</td>
                                            <td class="text-end">{{ number_format($weights['max_landing_weight']) }} kg</td>
                                            <td class="text-end">{{ number_format($weights['max_landing_weight_diff']) }} kg</td>
                                            <td class="text-center">
                                                <i
                                                    class="bi bi-{{ $weights['is_landing_weight_ok'] ? 'check text-success' : 'x text-danger' }}"></i>
                                            </td>
                                        </tr>
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
