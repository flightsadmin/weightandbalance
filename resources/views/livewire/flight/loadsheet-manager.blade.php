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
                        </table>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4>Edition: {{ $loadsheet->edition }}</h4>
                        <span class="badge bg-{{ $loadsheet->final ? 'success' : 'warning' }}">
                            {{ $loadsheet->final ? 'Final' : 'Draft' }}
                        </span>
                    </div>
                </div>

                <!-- Load Distribution -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Load Distribution</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Hold</th>
                                            <th class="text-end">Cargo</th>
                                            <th class="text-end">Baggage</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($loadsheet->payload_distribution['loads'] as $holdId => $load)
                                            <tr>
                                                <td>{{ $load['hold_name'] }}</td>
                                                <td class="text-end">{{ number_format($load['cargo_weight']) }}</td>
                                                <td class="text-end">{{ number_format($load['baggage_weight']) }}</td>
                                                <td class="text-end">{{ number_format($load['total_weight']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Passenger & Crew Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <h6>Passengers</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Male</td>
                                                <td class="text-end">
                                                    {{ $loadsheet->payload_distribution['passenger_distribution']['male'] ?? 0 }}</td>
                                            </tr>
                                            <tr>
                                                <td>Female</td>
                                                <td class="text-end">
                                                    {{ $loadsheet->payload_distribution['passenger_distribution']['female'] ?? 0 }}</td>
                                            </tr>
                                            <tr>
                                                <td>Child</td>
                                                <td class="text-end">
                                                    {{ $loadsheet->payload_distribution['passenger_distribution']['child'] ?? 0 }}</td>
                                            </tr>
                                            <tr>
                                                <td>Infant</td>
                                                <td class="text-end">
                                                    {{ $loadsheet->payload_distribution['passenger_distribution']['infant'] ?? 0 }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-6">
                                        <h6>Crew</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Flight Deck</td>
                                                <td class="text-end">{{ $loadsheet->payload_distribution['crew_distribution']['deck'] }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Cabin</td>
                                                <td class="text-end">{{ $loadsheet->payload_distribution['crew_distribution']['cabin'] }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weights Summary -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Weight Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Operating Weights</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>DOW</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['dry_operating']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>ZFW</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['zero_fuel']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>TOW</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['take_off']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>LDW</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['landing']) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Payload Weights</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Passengers</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['passenger']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Cargo</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['cargo']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Baggage</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['baggage']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Crew</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['weights']['crew']) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Fuel Figures</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Block Fuel</td>
                                                <td class="text-end">{{ number_format($loadsheet->payload_distribution['fuel']['block']) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Take Off Fuel</td>
                                                <td class="text-end">
                                                    {{ number_format($loadsheet->payload_distribution['fuel']['take_off']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Trip Fuel</td>
                                                <td class="text-end">{{ number_format($loadsheet->payload_distribution['fuel']['trip']) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Taxi Fuel</td>
                                                <td class="text-end">{{ number_format($loadsheet->payload_distribution['fuel']['taxi']) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Data Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Balance Data</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Condition</th>
                                        <th class="text-end">Weight</th>
                                        <th class="text-end">Index</th>
                                        <th class="text-end">% MAC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Zero Fuel</td>
                                        <td class="text-end">{{ number_format($loadsheet->payload_distribution['weights']['zero_fuel']) }}
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($loadsheet->payload_distribution['balance']['indices']['zfw'], 1) }}</td>
                                        <td class="text-end">
                                            {{ number_format($loadsheet->payload_distribution['balance']['mac_percentages']['zfw'], 1) }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Take Off</td>
                                        <td class="text-end">{{ number_format($loadsheet->payload_distribution['weights']['take_off']) }}
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($loadsheet->payload_distribution['balance']['indices']['tow'], 1) }}</td>
                                        <td class="text-end">
                                            {{ number_format($loadsheet->payload_distribution['balance']['mac_percentages']['tow'], 1) }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Landing</td>
                                        <td class="text-end">{{ number_format($loadsheet->payload_distribution['weights']['landing']) }}
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($loadsheet->payload_distribution['balance']['indices']['ldw'], 1) }}</td>
                                        <td class="text-end">
                                            {{ number_format($loadsheet->payload_distribution['balance']['mac_percentages']['ldw'], 1) }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                                            <p><strong>Released At:</strong> {{ $loadsheet->released_at->format('d-M-Y H:i') }}</p>
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
