<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Load Sheet</h3>
            <div class="d-flex gap-2">
                @if ($loadsheet && !$loadsheet->final)
                    <button class="btn btn-success btn-sm" wire:click="finalizeLoadsheet">
                        <i class="bi bi-check2-circle"></i> Finalize Loadsheet
                    </button>
                @endif
                <button class="btn btn-primary btn-sm" wire:click="generateLoadsheet"
                    {{ !$flight->fuel || !$loadplan || $loadplan->status !== 'released' ? 'disabled' : '' }}>
                    <i class="bi bi-plus-circle"></i> Generate New Loadsheet
                </button>
            </div>
        </div>

        @if ($loadsheet)
            <div class="card-body">
                <!-- Header Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Flight:</th>
                                <td>{{ $flight->flight_number }}</td>
                                <th>Date:</th>
                                <td>{{ $flight->scheduled_departure_time->format('d-M-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Route:</th>
                                <td>{{ $flight->departure_airport }} - {{ $flight->arrival_airport }}</td>
                                <th>A/C Reg:</th>
                                <td>{{ $flight->aircraft->registration_number }}</td>
                            </tr>
                            <tr>
                                <th>Crew:</th>
                                <td>{{ $flight->fuel->crew }}</td>
                                <th>Pantry:</th>
                                <td>{{ $flight->fuel->pantry }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4>Edition: {{ $loadsheet->edition }}</h4>
                        <span class="badge bg-{{ $loadsheet->final ? 'success' : 'warning' }}">
                            {{ $loadsheet->final ? 'Final' : 'Draft' }}
                        </span>
                    </div>
                </div>

                <!-- Weight Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Weight Summary</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Basic Weight:</th>
                                <td>{{ number_format($loadsheet->payload_distribution['weights']['basic']) }} kg</td>
                                <th>Total Payload:</th>
                                <td>{{ number_format($loadsheet->payload_distribution['weights']['total_payload']) }} kg</td>
                            </tr>
                            <tr>
                                <th>Zero Fuel Weight:</th>
                                <td>{{ number_format($loadsheet->payload_distribution['weights']['zero_fuel']) }} kg</td>
                                <th>Total Fuel:</th>
                                <td>{{ number_format($loadsheet->payload_distribution['weights']['total_fuel']) }} kg</td>
                            </tr>
                            <tr>
                                <th>Take Off Weight:</th>
                                <td>{{ number_format($loadsheet->payload_distribution['weights']['takeoff']) }} kg</td>
                                <th>Total Weight:</th>
                                <td>{{ number_format($loadsheet->payload_distribution['weights']['total']) }} kg</td>
                            </tr>
                            <tr>
                                <th>Landing Weight:</th>
                                <td colspan="3">{{ number_format($loadsheet->payload_distribution['weights']['landing']) }} kg</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Load Distribution -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Load Distribution</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Count</th>
                                    <th>Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Passengers</td>
                                    <td>{{ $loadsheet->payload_distribution['loads']['passengers']['count'] }}</td>
                                    <td>{{ number_format($loadsheet->payload_distribution['loads']['passengers']['weight']) }} kg</td>
                                </tr>
                                <tr>
                                    <td>Baggage</td>
                                    <td>{{ $loadsheet->payload_distribution['loads']['baggage']['count'] }}</td>
                                    <td>{{ number_format($loadsheet->payload_distribution['loads']['baggage']['weight']) }} kg</td>
                                </tr>
                                <tr>
                                    <td>Cargo</td>
                                    <td>{{ $loadsheet->payload_distribution['loads']['cargo']['count'] }}</td>
                                    <td>{{ number_format($loadsheet->payload_distribution['loads']['cargo']['weight']) }} kg</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Signatures Section -->
                @if ($loadsheet->final)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Created By:</strong> {{ $loadsheet->creator->name ?? 'N/A' }}</p>
                                            <p><strong>Created At:</strong> {{ $loadsheet->created_at->format('d-M-Y H:i') }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Released By:</strong> {{ $loadsheet->releaser->name ?? 'N/A' }}</p>
                                            <p><strong>Released At:</strong> {{ $loadsheet->released_at?->format('d-M-Y H:i') ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="card-body">
                <div class="text-center py-4">
                    <p class="text-muted">No loadsheet generated yet.</p>
                    @if (!$flight->fuel)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Fuel data must be added before generating loadsheet.
                        </div>
                    @endif
                    @if (!$loadplan || $loadplan->status !== 'released')
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Load plan must be released before generating loadsheet.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
