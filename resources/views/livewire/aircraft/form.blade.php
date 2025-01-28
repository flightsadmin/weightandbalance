<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">{{ $aircraft ? 'Edit' : 'Create' }} Aircraft</h2>
            <a wire:navigate href="{{ route('aircraft.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="airline_id" class="form-label">Airline</label>
                            <select wire:model.live="airline_id" id="airline_id"
                                class="form-select @error('airline_id') is-invalid @enderror">
                                <option value="">Select Airline</option>
                                @foreach ($airlines as $airline)
                                    <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                                @endforeach
                            </select>
                            @error('airline_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="registration" class="form-label">Registration</label>
                            <input type="text" wire:model.live="registration" id="registration"
                                class="form-control @error('registration') is-invalid @enderror">
                            @error('registration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <input type="text" wire:model.live="type" id="type"
                                class="form-control @error('type') is-invalid @enderror">
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" wire:model.live="model" id="model"
                                class="form-control @error('model') is-invalid @enderror">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="passenger_capacity" class="form-label">Passenger Capacity</label>
                            <input type="number" wire:model.live="passenger_capacity" id="passenger_capacity"
                                class="form-control @error('passenger_capacity') is-invalid @enderror">
                            @error('passenger_capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cargo_capacity" class="form-label">Cargo Capacity (kg)</label>
                            <input type="number" wire:model.live="cargo_capacity" id="cargo_capacity"
                                class="form-control @error('cargo_capacity') is-invalid @enderror">
                            @error('cargo_capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="empty_weight" class="form-label">Empty Weight (kg)</label>
                            <input type="number" wire:model.live="empty_weight" id="empty_weight"
                                class="form-control @error('empty_weight') is-invalid @enderror">
                            @error('empty_weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_takeoff_weight" class="form-label">Max Takeoff Weight (kg)</label>
                                    <input type="number" wire:model.live="max_takeoff_weight" id="max_takeoff_weight"
                                        class="form-control @error('max_takeoff_weight') is-invalid @enderror">
                                    @error('max_takeoff_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_fuel_capacity" class="form-label">Max Fuel Capacity (L)</label>
                                    <input type="number" wire:model.live="max_fuel_capacity" id="max_fuel_capacity"
                                        class="form-control @error('max_fuel_capacity') is-invalid @enderror">
                                    @error('max_fuel_capacity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select wire:model.live="status" id="status"
                                class="form-select @error('status') is-invalid @enderror">
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea wire:model.live="notes" id="notes" rows="3"
                                class="form-control @error('notes') is-invalid @enderror"></textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a wire:navigate href="{{ route('aircraft.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save"></i>
                        {{ $aircraft ? 'Update' : 'Create' }} Aircraft
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
