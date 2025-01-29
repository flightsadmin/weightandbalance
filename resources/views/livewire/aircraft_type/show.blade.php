<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3>{{ $aircraftType->name }}</h3>
        </div>
    </div>

    <div class="card-body">
        <div class="row gx-2">
            <div class="col-md-3">
                <div class="list-group">
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'overview' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'overview')">
                        <i class="bi bi-info-circle"></i> Overview
                    </button>
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'holds' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'holds')">
                        <i class="bi bi-box"></i> Cargo Holds
                    </button>
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'aircraft' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'aircraft')">
                        <i class="bi bi-airplane"></i> Aircraft
                    </button>
                </div>
            </div>

            <div class="col-md-9">
                @if ($activeTab === 'overview')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Aircraft Type Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-0">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Code:</th>
                                            <td>{{ $aircraftType->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td>{{ $aircraftType->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Manufacturer:</th>
                                            <td>{{ $aircraftType->manufacturer }}</td>
                                        </tr>
                                        <tr>
                                            <th>Category:</th>
                                            <td>{{ $aircraftType->category }}</td>
                                        </tr>
                                        <tr>
                                            <th>Max Range:</th>
                                            <td>{{ number_format($aircraftType->max_range) }} nm</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Max Passengers:</th>
                                            <td>{{ number_format($aircraftType->max_passengers) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Cargo Capacity:</th>
                                            <td>{{ number_format($aircraftType->cargo_capacity) }} kg</td>
                                        </tr>
                                        <tr>
                                            <th>Fuel Capacity:</th>
                                            <td>{{ number_format($aircraftType->max_fuel_capacity) }} L</td>
                                        </tr>
                                        <tr>
                                            <th>Max Takeoff Weight:</th>
                                            <td>{{ number_format($aircraftType->max_takeoff_weight) }} kg</td>
                                        </tr>
                                        <tr>
                                            <th>Max Landing Weight:</th>
                                            <td>{{ number_format($aircraftType->max_landing_weight) }} kg</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'holds')
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Cargo Holds</h5>
                            <button class="btn btn-primary btn-sm" wire:click="createHold" data-bs-toggle="modal"
                                data-bs-target="#holdFormModal">
                                <i class="bi bi-plus-circle"></i> Add Hold
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="modal fade" id="holdFormModal" tabindex="-1" wire:ignore.self>
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form wire:submit="saveHold">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $editingHold ? 'Edit' : 'Add' }} Hold</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Name</label>
                                                        <select name="name" class="form-select form-select-sm"
                                                            wire:model.live="holdForm.name">
                                                            <option value="">Select Name</option>
                                                            <option value="Aft Hold">Aft Hold</option>
                                                            <option value="Fwd Hold">Fwd Hold</option>
                                                            <option value="Bulk Hold">Bulk Hold</option>
                                                        </select>
                                                        @error('holdForm.name')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Code</label>
                                                        <select class="form-select form-select-sm" wire:model.live="holdForm.code">
                                                            <option value="">Select Code</option>
                                                            <option value="AH">AH (Aft Hold)</option>
                                                            <option value="FH">FH (Fwd Hold)</option>
                                                            <option value="BH">BH (Bulk Hold)</option>
                                                        </select>
                                                        @error('holdForm.code')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Position</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="holdForm.position">
                                                        @error('holdForm.position')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Max Weight (kg)</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="holdForm.max_weight">
                                                        @error('holdForm.max_weight')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="holdForm.is_active">
                                                            <label class="form-check-label">Active</label>
                                                        </div>
                                                    </div>

                                                    <!-- Hold Positions Section -->
                                                    <div class="col-12">
                                                        <div class="card">
                                                            <div
                                                                class="card-header d-flex justify-content-between align-items-center py-2">
                                                                <h6 class="mb-0">Hold Positions</h6>
                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                    wire:click="addHoldPosition">
                                                                    <i class="bi bi-plus-circle"></i> Add Position
                                                                </button>
                                                            </div>
                                                            <div class="card-body p-2">
                                                                @if (empty($holdForm['positions']))
                                                                    <p class="text-center text-muted small mb-0">No positions defined.
                                                                        Click
                                                                        "Add Position" to create one.</p>
                                                                @else
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm mb-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Row</th>
                                                                                    <th>Side</th>
                                                                                    <th>Max Weight</th>
                                                                                    <th>Active</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($holdForm['positions'] as $index => $position)
                                                                                    <tr wire:key="position-{{ $index }}">
                                                                                        <td style="width: 80px">
                                                                                            <input type="number"
                                                                                                class="form-control form-control-sm"
                                                                                                wire:model="holdForm.positions.{{ $index }}.row">
                                                                                            @error("holdForm.positions.{$index}.row")
                                                                                                <div class="text-danger small">
                                                                                                    {{ $message }}</div>
                                                                                            @enderror
                                                                                        </td>
                                                                                        <td style="width: 100px">
                                                                                            @if ($holdForm['code'] !== 'BH')
                                                                                                <select
                                                                                                    class="form-select form-select-sm"
                                                                                                    wire:model="holdForm.positions.{{ $index }}.side">
                                                                                                    <option value="L">Left
                                                                                                    </option>
                                                                                                    <option value="R">Right
                                                                                                    </option>
                                                                                                </select>
                                                                                            @else
                                                                                                <span class="text-muted">-</span>
                                                                                            @endif
                                                                                            @error("holdForm.positions.{$index}.side")
                                                                                                <div class="text-danger small">
                                                                                                    {{ $message }}</div>
                                                                                            @enderror
                                                                                        </td>
                                                                                        <td>
                                                                                            <input type="number"
                                                                                                class="form-control form-control-sm"
                                                                                                wire:model="holdForm.positions.{{ $index }}.max_weight">
                                                                                            @error("holdForm.positions.{$index}.max_weight")
                                                                                                <div class="text-danger small">
                                                                                                    {{ $message }}</div>
                                                                                            @enderror
                                                                                        </td>
                                                                                        <td style="width: 80px">
                                                                                            <div class="form-check form-switch">
                                                                                                <input class="form-check-input"
                                                                                                    type="checkbox"
                                                                                                    wire:model="holdForm.positions.{{ $index }}.is_active">
                                                                                            </div>
                                                                                        </td>
                                                                                        <td class="text-end" style="width: 50px">
                                                                                            <button type="button"
                                                                                                class="btn btn-sm btn-link text-danger p-0"
                                                                                                wire:click="removeHoldPosition({{ $index }})">
                                                                                                <i class="bi bi-trash"></i>
                                                                                            </button>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-primary">Save Hold</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Position</th>
                                        <th>Max Weight</th>
                                        <th>Status</th>
                                        <th>Positions</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($holds as $hold)
                                        <tr wire:key="{{ $hold->id }}">
                                            <td>{{ $hold->name }}</td>
                                            <td>{{ $hold->code }}</td>
                                            <td>{{ $hold->position }}</td>
                                            <td>{{ number_format($hold->max_weight) }} kg</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $hold->is_active ? 'success' : 'danger' }}">
                                                    {{ $hold->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ number_format($hold->positions->count()) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" wire:click="editHold({{ $hold->id }})"
                                                    data-bs-toggle="modal" data-bs-target="#holdFormModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    wire:click="deleteHold({{ $hold->id }})"
                                                    wire:confirm="Are you sure you want to delete this hold?">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No holds defined.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'aircraft')
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Aircraft of this Type</h5>
                            <a href="{{ route('aircraft.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add Aircraft
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Registration</th>
                                        <th>Airline</th>
                                        <th>Manufacture Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($aircraft as $plane)
                                        <tr wire:key="{{ $plane->id }}">
                                            <td>{{ $plane->registration_number }}</td>
                                            <td>{{ $plane->airline->iata_code }}</td>
                                            <td>{{ $plane->manufacture_date?->format('M d, Y') }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $plane->active ? 'success' : 'danger' }}">
                                                    {{ $plane->active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('aircraft.show', $plane) }}"
                                                    class="btn btn-sm btn-link">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('aircraft.edit', $plane) }}"
                                                    class="btn btn-sm btn-link">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No aircraft found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-between align-items-center">
                                {{ $aircraft->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on(['hold-saved', 'hold-updated'], () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('holdFormModal'));
                modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Hold saved successfully',
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true,
                    position: 'top-end',
                });
            });
        </script>
    @endscript
</div>
