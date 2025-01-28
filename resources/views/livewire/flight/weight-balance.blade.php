<div>
    @if ($weightBalance)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title m-0">Weight & Balance Details</h2>
                <div>
                    <a wire:navigate href="{{ route('flights.show', $weightBalance->flight) }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
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
                                            <a wire:navigate href="{{ route('flights.show', $weightBalance->flight) }}"
                                                class="text-decoration-none">
                                                {{ $weightBalance->flight->flight_number }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Route</th>
                                        <td>{{ $weightBalance->flight->departure_airport }} → {{ $weightBalance->flight->arrival_airport }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Aircraft</th>
                                        <td>
                                            <a wire:navigate href="{{ route('aircraft.show', $weightBalance->flight->aircraft) }}"
                                                class="text-decoration-none">
                                                {{ $weightBalance->flight->aircraft->registration_number }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-{{ $weightBalance->within_limits ? 'success' : 'danger' }}">
                                                {{ $weightBalance->within_limits ? 'Within Limits' : 'Exceeds Limits' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Scheduled Departure</th>
                                        <td>{{ $weightBalance->flight->scheduled_departure_time->format('d-m-Y H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title m-0">Weight Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th class="w-25">Zero Fuel Weight</th>
                                        <td>{{ number_format($weightBalance->zero_fuel_weight) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Takeoff Fuel</th>
                                        <td>{{ number_format($weightBalance->takeoff_fuel_weight) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Takeoff Weight</th>
                                        <td>{{ number_format($weightBalance->takeoff_weight) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Landing Fuel</th>
                                        <td>{{ number_format($weightBalance->landing_fuel_weight) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Landing Weight</th>
                                        <td>{{ number_format($weightBalance->landing_weight) }} kg</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title m-0">Load Details</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th class="w-25">Passengers</th>
                                        <td>{{ number_format($weightBalance->passenger_weight_total) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Baggage</th>
                                        <td>{{ number_format($weightBalance->baggage_weight_total) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Cargo</th>
                                        <td>{{ number_format($weightBalance->cargo_weight_total) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Crew</th>
                                        <td>{{ number_format($weightBalance->crew_weight_total) }} kg</td>
                                    </tr>
                                    <tr>
                                        <th>Center of Gravity</th>
                                        <td>{{ $weightBalance->center_of_gravity }}%</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($weightBalance->notes)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title m-0">Notes</h5>
                                </div>
                                <div class="card-body">
                                    {{ $weightBalance->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Loadsheet not finalized.
        </div>
    @endif
</div>
