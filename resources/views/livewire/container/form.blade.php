<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">{{ $container ? 'Edit' : 'Add' }} Container</h2>
            <a wire:navigate href="{{ route('containers.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Containers
            </a>
        </div>

        <div class="card-body">
            <form wire:submit="save">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Flight</label>
                            <select wire:model="flight_id" class="form-select" required>
                                <option value="">Select Flight</option>
                                @foreach ($flights as $flight)
                                    <option value="{{ $flight->id }}">
                                        {{ $flight->flight_number }} ({{ $flight->departure_airport }} → {{ $flight->arrival_airport }})
                                    </option>
                                @endforeach
                            </select>
                            @error('flight_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Container Number</label>
                            <input type="text" wire:model="container_number" class="form-control" required>
                            @error('container_number')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select wire:model="type" class="form-select" required>
                                <option value="baggage">Baggage</option>
                                <option value="cargo">Cargo</option>
                            </select>
                            @error('type')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Compartment</label>
                            <select wire:model="compartment" class="form-select" required>
                                <option value="">Select Compartment</option>
                                <option value="forward">Forward</option>
                                <option value="aft">Aft</option>
                                <option value="bulk">Bulk</option>
                            </select>
                            @error('compartment')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tare Weight (kg)</label>
                            <input type="number" wire:model="tare_weight" class="form-control" step="1" required>
                            @error('tare_weight')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Maximum Weight (kg)</label>
                            <input type="number" wire:model="max_weight" class="form-control" step="1" required>
                            @error('max_weight')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="3"></textarea>
                            @error('notes')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-sm btn-primary float-end">
                            {{ $container ? 'Update' : 'Create' }} Container
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
