<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex gap-3 align-items-center">
            <div class="col-md-4">
                <select wire:model.live="selectedAirlineId" class="form-select form-select-sm">
                    <option value="">Select Airline</option>
                    @foreach ($airlines as $airline)
                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="search" wire:model.live="search" class="form-control form-control-sm"
                    placeholder="Search aircraft types...">
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center gap-2">
            @if ($selectedAirlineId)
                <button class="btn btn-sm btn-primary">{{ $selectedAirline->name }}</button>
            @endif
            <button class="btn btn-primary btn-sm" wire:click="$toggle('showForm')" data-bs-toggle="modal"
                data-bs-target="#aircraftTypeFormModal">
                <i class="bi bi-plus-circle"></i> Add Aircraft Type
            </button>
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Manufacturer</th>
                    <th>Category</th>
                    <th>Passengers</th>
                    <th>Cargo (kg)</th>
                    <th>Fuel (L)</th>
                    <th>MTOW (kg)</th>
                    <th>Range (nm)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aircraftTypes as $type)
                    <tr wire:key="{{ $type->id }}">
                        <td>
                            <a wire:navigate href="{{ route('aircraft_types.show', $type) }}"
                                class="text-decoration-none">{{ $type->code }}</a>
                        </td>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->manufacturer }}</td>
                        <td>{{ $type->category }}</td>
                        <td>{{ number_format($type->max_passengers) }}</td>
                        <td>{{ number_format($type->cargo_capacity) }}</td>
                        <td>{{ number_format($type->max_fuel_capacity) }}</td>
                        <td>{{ number_format($type->max_takeoff_weight) }}</td>
                        <td>{{ number_format($type->max_range) }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-link" wire:click="edit({{ $type->id }})"
                                data-bs-toggle="modal" data-bs-target="#aircraftTypeFormModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-link text-danger" wire:click="delete({{ $type->id }})"
                                wire:confirm="Are you sure you want to remove this aircraft type?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No aircraft types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        {{ $aircraftTypes->links() }}
    </div>

    <!-- Modal -->
    <div class="modal fade" id="aircraftTypeFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingAircraftType ? 'Edit' : 'Add' }} Aircraft Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.code">
                                @error('form.code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.name">
                                @error('form.name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.manufacturer">
                                @error('form.manufacturer')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select class="form-select form-select-sm" wire:model="form.category">
                                    <option value="">Select Category</option>
                                    <option value="Narrow-body">Narrow-body</option>
                                    <option value="Wide-body">Wide-body</option>
                                    <option value="Regional">Regional</option>
                                </select>
                                @error('form.category')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Passengers</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_passengers">
                                @error('form.max_passengers')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Range (nm)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_range">
                                @error('form.max_range')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Empty Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.empty_weight">
                                @error('form.empty_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Zero Fuel Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_zero_fuel_weight">
                                @error('form.max_zero_fuel_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Takeoff Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_takeoff_weight">
                                @error('form.max_takeoff_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Landing Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_landing_weight">
                                @error('form.max_landing_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cargo Capacity (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.cargo_capacity">
                                @error('form.cargo_capacity')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Fuel Capacity (L)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_fuel_capacity">
                                @error('form.max_fuel_capacity')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Deck Crew</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_deck_crew">
                                @error('form.max_deck_crew')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Cabin Crew</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_cabin_crew">
                                @error('form.max_cabin_crew')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Aircraft Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add to modal form -->
    @if ($showForm && $selectedAirlineId)
        <div class="card-body alert alert-info alert-sm text-center" style="border-radius: 0">
            New aircraft type will be automatically associated with {{ $selectedAirline->name }}.
        </div>
    @endif

    @script
        <script>
            $wire.on('aircraft-type-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('aircraftTypeFormModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
