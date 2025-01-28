<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="col-md-4">
            <input type="search" wire:model.live="search" class="form-control form-control-sm"
                placeholder="Search by name, ticket or seat number...">
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">{{ $passengers->count() }} Passengers</span>
            <span class="badge bg-success">{{ $passengers->where('acceptance_status', 'accepted')->count() }} Accepted</span>
            <span class="badge bg-warning">{{ $passengers->where('acceptance_status', 'standby')->count() }} Standby</span>
            <span class="badge bg-danger">{{ $passengers->where('acceptance_status', 'offloaded')->count() }} Offloaded</span>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">{{ $passengers->count() }} Passengers</span>
            <span class="badge bg-success">{{ $passengers->where('boarding_status', 'boarded')->count() }} Boarded</span>
            <span class="badge bg-danger">{{ $passengers->where('boarding_status', 'unboarded')->count() }} Unboarded</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" wire:click="$toggle('showForm')" data-bs-toggle="modal"
                data-bs-target="#passengerFormModal">
                <i class="bi bi-plus-circle"></i> Add Passenger
            </button>
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    @unless ($flight)
                        <th>Flight</th>
                    @endunless
                    <th>Ticket Number</th>
                    <th>Seat</th>
                    <th>Baggage</th>
                    <th>Acceptance</th>
                    <th>Boarding</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($passengers as $passenger)
                    <tr wire:key="{{ $passenger->id }}">
                        <td>{{ $passenger->name }}</td>
                        <td>{{ ucfirst($passenger->type ?? 'N/A') }}</td>
                        @unless ($flight)
                            <td>
                                @if ($passenger->flight)
                                    {{ $passenger->flight->flight_number }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endunless
                        <td>{{ $passenger->ticket_number }}</td>
                        <td>{{ $passenger->seat_number }}</td>
                        <td>{{ $passenger->baggage_count }} <i class="bi bi-luggage-fill"></i> pcs</td>
                        <td>
                            <div class="dropdown d-inline">
                                <button
                                    class="btn btn-sm btn-{{ $passenger->acceptance_status === 'accepted' ? 'success' : ($passenger->acceptance_status === 'standby' ? 'warning' : 'danger') }} dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown">
                                    {{ str_replace('_', ' ', ucwords($passenger->acceptance_status)) }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'booked')">
                                            <i class="bi bi-bookmark-check-fill text-secondary"></i> Booked
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'standby')">
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
                            <div class="dropdown d-inline">
                                <button
                                    class="btn btn-sm btn-{{ $passenger->boarding_status === 'boarded' ? 'success' : ($passenger->boarding_status === 'unboarded' ? 'danger' : 'warning') }} dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown">
                                    {{ str_replace('_', ' ', ucwords($passenger->boarding_status)) }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" wire:click="updateBoardingStatus({{ $passenger->id }}, 'boarded')">
                                            <i class="bi bi-check-circle text-success"></i> Boarded
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item"
                                            wire:click="updateBoardingStatus({{ $passenger->id }}, 'unboarded')">
                                            <i class="bi bi-x-circle text-danger"></i> Unboarded
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-end">
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

    <div class="d-flex justify-content-between align-items-center">
        {{ $passengers->links() }}
    </div>

    <!-- Modal -->
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
                                <label class="form-label">Seat Number</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.seat_number">
                                @error('form.seat_number')
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
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control form-control-sm" wire:model="form.notes" rows="2"></textarea>
                                @error('form.notes')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
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

    @script
        <script>
            $wire.on('passenger-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('passengerFormModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
