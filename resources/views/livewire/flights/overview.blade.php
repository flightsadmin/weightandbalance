<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Flight Details</h2>
            <div>
                <a wire:navigate href="{{ route('flights.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to flights
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
                                    <th class="w-25">Flight Number</th>
                                    <td>{{ $flight->flight_number }}
                                        <span class="dropdown d-inline float-end">
                                            <button
                                                class="btn btn-sm btn-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }} dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                {{ str(str_replace('_', ' ', $flight->status))->title() }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateStatus('scheduled')">
                                                        <i class="bi bi-bookmark-check-fill text-secondary"></i> Scheduled
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateStatus('boarding')">
                                                        <i class="bi bi-hourglass-split text-warning"></i> Boarding
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="openTimeModal({{ $flight->id }}, 'ATD')"
                                                        data-bs-toggle="modal" data-bs-target="#timeModal">
                                                        <i class="bi bi-airplane-fill d-inline-block text-warning"
                                                            style="transform: rotate(45deg);"></i> Departed
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="openTimeModal({{ $flight->id }}, 'ATA')"
                                                        data-bs-toggle="modal" data-bs-target="#timeModal">
                                                        <i class="bi bi-airplane-fill d-inline-block text-success"
                                                            style="transform: rotate(110deg);"></i> Arrived
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateStatus('cancelled')">
                                                        <i class="bi bi-x-circle text-danger"></i> Cancelled
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateStatus('post_departure')">
                                                        <i class="bi bi-clock-history text-warning"></i> Post Departure
                                                    </button>
                                                </li>
                                            </ul>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Airline</th>
                                    <td>{{ $flight->airline->name }}</td>
                                </tr>
                                <tr>
                                    <th>Aircraft</th>
                                    <td>{{ $flight->aircraft->registration_number }} ({{ $flight->aircraft->type->name }})

                                        <span class="dropdown d-inline float-end">
                                            <button
                                                class="btn btn-sm btn-secondary dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown">
                                                {{ $flight->aircraft->registration_number }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach ($flight->airline->aircraft as $registration)
                                                    <li>
                                                        <button class="dropdown-item"
                                                            wire:click="updateRegistration('{{ $registration->id }}')"
                                                            wire:confirm="Are you sure you want to update the aircraft? All containers will be moved to unplanned.">
                                                            {{ ucfirst($registration->registration_number) }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Route</th>
                                    <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                                </tr>
                                <tr>
                                    <th>Schedule</th>
                                    <td>
                                        <div>STD: {{ $flight->scheduled_departure_time->format('d M Y H:i') }}
                                            <span class="small text-muted float-end">
                                                Scheduled Flight Duration:
                                                {{ $flight->scheduled_departure_time->diff($flight->scheduled_arrival_time)->format('%h:%I') }}
                                            </span>
                                        </div>
                                        <div>STA: {{ $flight->scheduled_arrival_time->format('d M Y H:i') }}</div>
                                    </td>
                                </tr>
                                @if ($flight->actual_departure_time && $flight->actual_arrival_time)
                                    <tr>
                                        <th>Actual</th>
                                        <td>
                                            <div>ATD: {{ $flight->actual_departure_time->format('d M Y H:i') }}
                                                <span class="small text-muted float-end">
                                                    Actual Flight Duration:
                                                    {{ $flight->actual_departure_time->diff($flight->actual_arrival_time)->format('%h:%I') }}
                                                </span>
                                            </div>
                                            <div>ATA: {{ $flight->actual_arrival_time->format('d M Y H:i') }}</div>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title m-0">Load Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Passengers</label>
                                        <h4>{{ $flight->passengers->count() }} PAX</h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Baggage</label>
                                        <h4>{{ $flight->baggage->count() }} pcs / {{ number_format($flight->baggage->sum('weight')) }} kg
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Cargo</label>
                                        <h4>{{ $flight->cargo->count() }} pcs / {{ number_format($flight->cargo->sum('weight')) }} kg</h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Total Deadload Weight</label>
                                        <h4>{{ number_format($flight->baggage->sum('weight') + $flight->cargo->sum('weight')) }} kg</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Update Modal -->
    <div class="modal fade" id="timeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Update {{ $timeType === 'ATD' ? 'Departure' : 'Arrival' }} Time
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedFlight)
                        <div class="flight-info mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Flight:</strong> {{ $selectedFlight->flight_number }}
                                </div>
                                <div>
                                    <strong>Route:</strong>
                                    {{ $selectedFlight->departure_airport }} -
                                    {{ $selectedFlight->arrival_airport }}
                                </div>
                                <div>
                                    <strong>{{ $timeType === 'ATD' ? 'STD' : 'STA' }}:</strong>
                                    {{ $timeType === 'ATD'
                                        ? $selectedFlight->scheduled_departure_time->format('H:i')
                                        : $selectedFlight->scheduled_arrival_time->format('H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $timeType === 'ATD' ? 'Actual Departure Time' : 'Actual Arrival Time' }}
                            </label>
                            <input type="datetime-local" class="form-control"
                                wire:model="timeForm.datetime">
                            @error('timeForm.datetime')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="updateFlightTime">
                        <i class="bi bi-check-circle"></i> Update Time
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('time-updated', () => {
                bootstrap.Modal.getInstance(document.getElementById('timeModal')).hide();
            });
        </script>
    @endscript
</div>
