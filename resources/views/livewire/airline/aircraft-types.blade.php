<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="col-md-4">
            <input type="search" wire:model.live="search" class="form-control form-control-sm"
                placeholder="Search aircraft types...">
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" wire:click="$toggle('showCreateForm')" data-bs-toggle="modal"
                data-bs-target="#createAircraftTypeModal">
                <i class="bi bi-plus-circle"></i> Create New Type
            </button>
            <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addAircraftTypeModal">
                <i class="bi bi-plus-circle"></i> Add Existing Type
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
                    <th>Aircraft</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($aircraftTypes as $type)
                    <tr wire:key="{{ $type->id }}">
                        <td>{{ $type->code }}</td>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->manufacturer }}</td>
                        <td>{{ $type->category }}</td>
                        <td>{{ $type->aircraft->where('airline_id', $airline->id)->count() }}</td>
                        <td class="text-end">
                            <a href="{{ route('aircraft_types.show', $type) }}" class="btn btn-sm btn-link">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-link text-danger"
                                wire:click="removeType({{ $type->id }})"
                                wire:confirm="Are you sure you want to remove this aircraft type?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No aircraft types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        {{ $aircraftTypes->links() }}
    </div>

    <!-- Create Aircraft Type Modal -->
    <div class="modal fade" id="createAircraftTypeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="createType">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Aircraft Type</h5>
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
                                <label class="form-label">Empty Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.empty_weight">
                                @error('form.empty_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Add other weight fields similarly -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Create Aircraft Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Aircraft Type Modal -->
    <div class="modal fade" id="addAircraftTypeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Aircraft Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($availableTypes->isEmpty())
                        <p class="text-center">No available aircraft types to add.</p>
                    @else
                        <div class="list-group">
                            @foreach ($availableTypes as $type)
                                <button type="button" class="list-group-item list-group-item-action"
                                    wire:click="addType({{ $type->id }})" data-bs-dismiss="modal">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $type->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $type->manufacturer }} | {{ $type->code }}</small>
                                        </div>
                                        <span class="badge bg-primary">{{ $type->category }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('alert', (event) => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('createAircraftTypeModal'));
                if (modal) modal.hide();
            });
        </script>
    @endscript
</div>
