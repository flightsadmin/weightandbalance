<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">{{ $airline ? 'Edit' : 'Create' }} Airline</h2>
            <a wire:navigate href="{{ route('airlines.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" wire:model.live="name" id="name"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="iata_code" class="form-label">IATA Code</label>
                            <input type="text" wire:model.live="iata_code" id="iata_code"
                                class="form-control @error('iata_code') is-invalid @enderror">
                            @error('iata_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" wire:model.live="country" id="country"
                                class="form-control @error('country') is-invalid @enderror">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" wire:model.live="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" wire:model.live="email" id="email"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea wire:model.live="address" id="address" rows="3"
                                class="form-control @error('address') is-invalid @enderror"></textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea wire:model.live="description" id="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model.live="active" id="active"
                                    class="form-check-input @error('active') is-invalid @enderror">
                                <label class="form-check-label" for="active">Active</label>
                                @error('active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a wire:navigate href="{{ route('airlines.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save"></i>
                        {{ $airline ? 'Update' : 'Create' }} Airline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
