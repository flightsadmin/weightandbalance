<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="col-md-4">
            <input type="search" wire:model.live="search" class="form-control form-control-sm"
                placeholder="Search by name, ticket or seat number...">
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">{{ $flight->passengers_count }} Passengers</span>
            <span class="badge bg-success">{{ $flight->accepted_count }} Accepted</span>
            <span class="badge bg-warning">{{ $flight->standby_count }} Standby</span>
            <span class="badge bg-danger">{{ $flight->offloaded_count }} Offloaded</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#passengerFormModal">
                <i class="bi bi-plus-circle"></i> Add Passenger
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th class="text-center">Specials</th>
                        <th>PNR</th>
                        <th>Ticket Number</th>
                        <th>Seat</th>
                        <th>Baggage</th>
                        <th>Acceptance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($passengers as $passenger)
                        <tr wire:key="{{ $passenger->id }}">
                            <td>
                                <a href="#" wire:click.prevent="showPassengerDetails({{ $passenger->id }})"
                                    class="text-decoration-none" data-bs-toggle="modal"
                                    data-bs-target="#passengerDetailsModal">
                                    {{ $passenger->name }}
                                </a>
                            </td>
                            <td>
                                {{ ucfirst($passenger->type ?? 'N/A') }}
                            </td>
                            <td class="text-center">
                                @if ($passenger->attributes)
                                    @foreach ($passenger->attributes as $key => $value)
                                        @if ($value && $key !== 'infant_name')
                                            <i class="bi bi-{{ match ($key) {
                                                'wchr', 'wchs', 'wchc' => 'person-wheelchair',
                                                'exst' => 'door-open',
                                                'stcr' => 'h-circle-fill',
                                                'deaf' => 'ear',
                                                'blind' => 'eye-fill',
                                                'dpna' => 'person-arms-up',
                                                'meda' => 'heart-pulse-fill',
                                                'infant' => 'person-standing-dress',
                                                default => 'person-check',
                                            } }}"
                                                title="{{ strtoupper($key) }}{{ $key === 'infant' ? ' - ' . $passenger->attributes['infant_name'] : '' }}"></i>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>{{ $passenger->pnr }}</td>
                            <td>{{ $passenger->ticket_number }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                    wire:click="showSeatModal({{ $passenger->id }})" data-bs-toggle="modal"
                                    data-bs-target="#seatModal">
                                    <i class="bi bi-person-check"></i> {{ $passenger->seat->designation ?? 'Assign' }}
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-link text-decoration-none text-reset"
                                    wire:click="editBaggage({{ $passenger->id }})" data-bs-toggle="modal"
                                    data-bs-target="#baggageModal">
                                    {{ $passenger->baggage_count }} <i class="bi bi-luggage-fill"></i> pcs
                                </button>
                            </td>
                            <td>
                                <div class="dropdown d-inline">
                                    <button
                                        class="btn btn-sm btn-{{ $passenger->acceptance_status === 'accepted' ? 'success' : ($passenger->acceptance_status === 'standby' ? 'warning' : 'danger') }} dropdown-toggle"
                                        type="button" data-bs-toggle="dropdown">
                                        {{ str_replace('_', ' ', ucwords($passenger->acceptance_status)) }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'booked')">
                                                <i class="bi bi-bookmark-check-fill text-secondary"></i> Booked
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'standby')">
                                                <i class="bi bi-hourglass-split text-warning"></i> Standby
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'accepted')">
                                                <i class="bi bi-check-circle text-success"></i> Accepted
                                            </button>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button class="dropdown-item"
                                                wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'offloaded')">
                                                <i class="bi bi-x-circle text-danger"></i> Offloaded
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-link" wire:click="edit({{ $passenger->id }})"
                                    data-bs-toggle="modal" data-bs-target="#passengerFormModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-link text-danger" wire:click="delete({{ $passenger->id }})"
                                    wire:confirm="Are you sure you want to remove this passenger?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $flight ? 6 : 7 }}" class="text-center">No passengers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>
            {{ $passengers->links() }}
        </div>
    </div>

    <!-- Passenger Form Modal -->
    <div class="modal fade" id="passengerFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingPassenger ? 'Edit' : 'Add' }} Passenger</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.name">
                                @error('form.name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Passenger Type</label>
                                <select class="form-select form-select-sm" wire:model="form.type">
                                    <option value="">Select Type</option>
                                    <option value="male">Adult Male</option>
                                    <option value="female">Adult Female</option>
                                    <option value="child">Child (2-11 yrs)</option>
                                    <option value="infant">Infant (0-2 yrs)</option>
                                </select>
                                @error('form.type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ticket Number</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.ticket_number">
                                @error('form.ticket_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Special Requirements</label>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.wchr"
                                                id="wchr">
                                            <label class="form-check-label" for="wchr">WCHR</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.wchs"
                                                id="wchs">
                                            <label class="form-check-label" for="wchs">WCHS</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.wchc"
                                                id="wchc">
                                            <label class="form-check-label" for="wchc">WCHC</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.exst"
                                                id="exst">
                                            <label class="form-check-label" for="exst">EXST</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.stcr"
                                                id="stcr">
                                            <label class="form-check-label" for="stcr">STCR</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.deaf"
                                                id="deaf">
                                            <label class="form-check-label" for="deaf">Deaf</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.blind"
                                                id="blind">
                                            <label class="form-check-label" for="blind">Blind</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.dpna"
                                                id="dpna">
                                            <label class="form-check-label" for="dpna">DPNA</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" wire:model="form.attributes.meda"
                                                id="meda">
                                            <label class="form-check-label" for="meda">MEDA</label>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input type="checkbox"
                                                    class="form-check-input"
                                                    wire:model.live="form.attributes.infant"
                                                    id="infant">
                                                <label class="form-check-label" for="infant">Infant</label>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($form['attributes']['infant'])
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label">Infant Name</label>
                                            <input type="text"
                                                class="form-control form-control-sm"
                                                wire:model="form.attributes.infant_name"
                                                placeholder="Enter infant name"
                                                autofocus>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Passenger</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Baggage Modal -->
    <div class="modal fade" id="baggageModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Baggage</h5>
                </div>
                <form wire:submit="saveBaggage">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pieces</label>
                                <input type="number" class="form-control form-control-sm" wire:model="pieces">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weight</label>
                                <input type="number" class="form-control form-control-sm" wire:model="weight">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Baggage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Passenger Details Modal -->
    <div class="modal fade" id="passengerDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Passenger Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedPassenger)
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-decoration-underline mb-3">Personal Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $selectedPassenger->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Type:</th>
                                        <td>{{ ucfirst($selectedPassenger->type) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Ticket Number:</th>
                                        <td>{{ $selectedPassenger->ticket_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Seat Number:</th>
                                        <td>{{ $selectedPassenger->seat->designation }}</td>
                                    </tr>
                                    <tr>
                                        <th>Acceptance Status:</th>
                                        <td>
                                            <span
                                                class="badge bg-{{ $selectedPassenger->acceptance_status === 'accepted'
                                                    ? 'success'
                                                    : ($selectedPassenger->acceptance_status === 'standby'
                                                        ? 'warning'
                                                        : 'danger') }}">
                                                {{ ucfirst($selectedPassenger->acceptance_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Boarding Status:</th>
                                        <td>
                                            <span
                                                class="badge bg-{{ $selectedPassenger->boarding_status === 'boarded' ? 'success' : 'danger' }}">
                                                {{ ucfirst($selectedPassenger->boarding_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-decoration-underline mb-3">Baggage Information</h6>
                                @if ($selectedPassenger->baggage->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tag Number</th>
                                                <th>Weight</th>
                                                <th>Container</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($selectedPassenger->baggage as $bag)
                                                <tr>
                                                    <td>{{ $bag->tag_number }}</td>
                                                    <td>{{ number_format($bag->weight) }} kg</td>
                                                    <td>
                                                        @if ($bag->container)
                                                            {{ $bag->container->container_number }}
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $bag->status === 'loaded' ? 'success' : ($bag->status === 'checked' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($bag->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-muted">No baggage found</div>
                                @endif
                            </div>
                        </div>
                        @if ($selectedPassenger->attributes)
                            <div class="mt-3">
                                <h6 class="text-decoration-underline">Special Requirements</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($selectedPassenger->attributes as $key => $value)
                                        @if ($value && $key !== 'infant_name')
                                            <span class="badge bg-info">
                                                {{ strtoupper($key) }}
                                                @if ($key === 'infant')
                                                    - {{ $selectedPassenger->attributes['infant_name'] }}
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary float-end" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seat Assignment Modal -->
    <div class="modal fade" id="seatModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title mt-0">Assign Seat</h5>
                    <div class="d-flex gap-3 ms-auto">
                        <span class="badge text-bg-secondary">Occupied</span>
                        <span class="badge text-bg-danger">Blocked</span>
                        <span class="badge text-bg-primary">Selected</span>
                        <span class="badge text-bg-light">Available</span>
                    </div>
                    <button type="button" class="btn-close mt-0" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="assignSeat">
                    <div class="modal-body">
                        <div class="seat-container">
                            <div class="seat-grid">
                                @php
                                    $allSeats = $seats
                                        ->pluck('seats')
                                        ->flatten()
                                        ->sortBy(['row', 'column']);
                                @endphp
                                @foreach ($allSeats->groupBy('row') as $row => $rowSeats)
                                    <div class="seat-row">
                                        @foreach ($rowSeats->sortBy('column') as $seat)
                                            <div class="seat-cell 
                                                {{ $seat->is_occupied ? 'occupied' : '' }}
                                                {{ $seat->is_blocked ? 'blocked' : '' }}
                                                {{ $selectedSeat == $seat->id ? 'selected' : '' }}"
                                                @if (!$seat->is_occupied) wire:contextmenu.prevent="toggleSeatBlock({{ $seat->id }})"
                                                    wire:click="selectSeat({{ $seat->id }})" @endif
                                                title="{{ $seat->is_blocked ? 'Right click to unblock' : 'Right click to block' }}">
                                                {{ $seat->designation }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <div class="text-muted small text-start">
                            <i class="bi bi-info-circle"></i> Right click on a seat to block/unblock it
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary" @if (!$selectedSeat) disabled @endif>
                                Assign Seat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .seat-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .seat-grid {
            display: table;
            border-spacing: 2px;
            border-collapse: separate;
            margin: 0 auto;
        }

        .seat-row {
            display: table-row;
        }

        .row-number {
            display: table-cell;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding-right: 4px;
            font-size: 0.80rem;
        }

        .seat-cell {
            display: table-cell;
            cursor: pointer;
            padding: 4px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
            text-align: center;
            min-width: 35px;
            height: 35px;
            vertical-align: middle;
            font-size: 0.80rem;
        }

        .seat-cell:hover:not(.occupied):not(.blocked) {
            background-color: #e9ecef;
        }

        .seat-cell.occupied {
            background-color: #6c757d;
            color: white;
            cursor: not-allowed;
        }

        .seat-cell.blocked {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }

        .seat-cell.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
    </style>

    @script
        <script>
            $wire.on('passenger-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('passengerFormModal'));
                modal.hide();
            });
            $wire.on('baggage-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('baggageModal'));
                modal.hide();
            });
            $wire.on('seat-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('seatModal')).hide();
            });
        </script>
    @endscript
</div>
