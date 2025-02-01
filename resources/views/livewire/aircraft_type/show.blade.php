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
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'zones' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'zones')">
                        <i class="bi bi-collection-play"></i> Cabin Zones
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
                                            <td>{{ number_format($aircraftType->max_passengers) }} pax</td>
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
                                                    <div class="col-md-5">
                                                        <label class="form-label">Code</label>
                                                        <select class="form-select form-select-sm" wire:model.live="holdForm.code">
                                                            <option value="">Select Code</option>
                                                            <option value="FH">FH (Forward Hold)</option>
                                                            <option value="AH">AH (Aft Hold)</option>
                                                            <option value="BH">BH (Bulk Hold)</option>
                                                        </select>
                                                        @error('holdForm.code')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model.live="holdForm.name" readonly>
                                                        @error('holdForm.name')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Status</label>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="holdForm.is_active">
                                                            <label class="form-check-label">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Position</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="holdForm.position">
                                                        @error('holdForm.position')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Max Weight (kg)</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="holdForm.max_weight">
                                                        @error('holdForm.max_weight')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Index</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="holdForm.index" step="0.0001">
                                                        @error('holdForm.index')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
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
                                                                    <p class="text-center text-muted small mb-0">
                                                                        No positions defined. Click "Add Position" to create one.
                                                                    </p>
                                                                @else
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm mb-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Row</th>
                                                                                    <th>Side</th>
                                                                                    <th>Max Weight</th>
                                                                                    <th>Index</th>
                                                                                    <th>Active</th>
                                                                                    <th>Action</th>
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
                                                                                        <td>
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
                                                                                        <td>
                                                                                            <input type="number"
                                                                                                class="form-control form-control-sm"
                                                                                                wire:model="holdForm.positions.{{ $index }}.index"
                                                                                                step="0.0001">
                                                                                            @error("holdForm.positions.{$index}.index")
                                                                                                <div class="text-danger small">
                                                                                                    {{ $message }}</div>
                                                                                            @enderror
                                                                                        </td>
                                                                                        <td>
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

                @if ($activeTab === 'zones')
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Cabin Zones</h3>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cabinZoneModal"
                                wire:ignore>
                                <i class="bi bi-plus-circle"></i> Add Zone
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Max Capacity</th>
                                            <th>Index</th>
                                            <th>Arm</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($aircraftType->cabinZones as $zone)
                                            <tr>
                                                <td>{{ $zone->name }}</td>
                                                <td>{{ number_format($zone->max_capacity) }} kg</td>
                                                <td>{{ number_format($zone->index, 4) }}</td>
                                                <td>{{ number_format($zone->arm, 4) }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" wire:click="editZone({{ $zone->id }})"
                                                        data-bs-toggle="modal" data-bs-target="#cabinZoneModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="deleteZone({{ $zone->id }})"
                                                        wire:confirm="Are you sure you want to delete this zone?">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No cabin zones defined</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'aircraft')
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Aircraft</h3>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#aircraftModal" wire:ignore>
                                <i class="bi bi-plus-circle"></i> Add Aircraft
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Registration</th>
                                            <th>Basic Weight</th>
                                            <th>Basic Index</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($aircraft as $plane)
                                            <tr>
                                                <td>{{ $plane->registration_number }}</td>
                                                <td>{{ number_format($plane->basic_weight) }} kg</td>
                                                <td>{{ number_format($plane->basic_index, 4) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $plane->active ? 'success' : 'danger' }}">
                                                        {{ $plane->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click="editAircraft({{ $plane->id }})"
                                                        data-bs-toggle="modal" data-bs-target="#aircraftModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="deleteAircraft({{ $plane->id }})"
                                                        wire:confirm="Are you sure you want to delete this aircraft?">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No aircraft found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $aircraft->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cabin Zone Modal -->
    <div class="modal fade" tabindex="-1" id="cabinZoneModal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingZone ? 'Edit' : 'Add' }} Cabin Zone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="saveZone">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Zone Name</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="zoneForm.name" required>
                                    @error('zoneForm.name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Capacity (kg)</label>
                                    <input type="number" class="form-control form-control-sm" wire:model="zoneForm.max_capacity"
                                        required>
                                    @error('zoneForm.max_capacity')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Index</label>
                                    <input type="number" step="0.0001" class="form-control form-control-sm"
                                        wire:model="zoneForm.index"
                                        required>
                                    @error('zoneForm.index')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Arm</label>
                                    <input type="number" step="0.0001" class="form-control form-control-sm" wire:model="zoneForm.arm"
                                        required>
                                    @error('zoneForm.arm')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Save Zone</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Aircraft Modal -->
    <div class="modal fade" tabindex="-1" id="aircraftModal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingAircraft ? 'Edit' : 'Add' }} Aircraft</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveAircraft">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="aircraftForm.registration_number" required>
                                    @error('aircraftForm.registration_number')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Basic Weight</label>
                                    <input type="number" class="form-control form-control-sm"
                                        wire:model="aircraftForm.basic_weight" required>
                                    @error('aircraftForm.basic_weight')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Basic Index</label>
                                    <input type="number" step="0.0001" class="form-control form-control-sm"
                                        wire:model="aircraftForm.basic_index" required>
                                    @error('aircraftForm.basic_index')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            wire:model="aircraftForm.active">
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control form-control-sm"
                                        wire:model="aircraftForm.remarks" rows="3"></textarea>
                                    @error('aircraftForm.remarks')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    Save Aircraft
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('hold-saved', () => {
                const holdModal = bootstrap.Modal.getInstance(document.getElementById('holdFormModal'));
                holdModal.hide();
            });

            $wire.on('zone-saved', () => {
                const cabinZoneModal = bootstrap.Modal.getInstance(document.getElementById('cabinZoneModal'));
                cabinZoneModal.hide();
            });

            $wire.on('aircraft-saved', () => {
                const aircraftModal = bootstrap.Modal.getInstance(document.getElementById('aircraftModal'));
                aircraftModal.hide();
            });
        </script>
    @endscript
</div>
