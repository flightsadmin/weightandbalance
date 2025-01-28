<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">{{ $flight ? 'Edit' : 'Create' }} Flight</h2>
            <a wire:navigate href="{{ route('flights.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="flight_number" class="form-label">Flight Number</label>
                            <input type="text" wire:model.live="flight_number" id="flight_number"
                                class="form-control @error('flight_number') is-invalid @enderror">
                            @error('flight_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
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
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="aircraft_id" class="form-label">Aircraft</label>
                            <select wire:model.live="aircraft_id" id="aircraft_id"
                                class="form-select @error('aircraft_id') is-invalid @enderror">
                                <option value="">Select Aircraft</option>
                                @foreach ($aircraft as $ac)
                                    <option value="{{ $ac->id }}">
                                        {{ $ac->registration_number }} ({{ $ac->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('aircraft_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="departure_airport" class="form-label">Departure Airport</label>
                            <input type="text" wire:model.live="departure_airport" id="departure_airport"
                                class="form-control @error('departure_airport') is-invalid @enderror">
                            @error('departure_airport')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="arrival_airport" class="form-label">Arrival Airport</label>
                            <input type="text" wire:model.live="arrival_airport" id="arrival_airport"
                                class="form-control @error('arrival_airport') is-invalid @enderror">
                            @error('arrival_airport')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select wire:model.live="status" id="status"
                                class="form-select @error('status') is-invalid @enderror">
                                <option value="scheduled">Scheduled</option>
                                <option value="boarding">Boarding</option>
                                <option value="departed">Departed</option>
                                <option value="arrived">Arrived</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="departure_time" class="form-label">Departure Time</label>
                                <input type="datetime-local" wire:model.live="scheduled_departure_time"
                                    id="scheduled_departure_time"
                                    class="form-control @error('scheduled_departure_time') is-invalid @enderror">
                                @error('scheduled_departure_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduled_arrival_time" class="form-label">Arrival Time</label>
                                <input type="datetime-local" wire:model.live="scheduled_arrival_time"
                                    id="scheduled_arrival_time"
                                    class="form-control @error('scheduled_arrival_time') is-invalid @enderror">
                                @error('scheduled_arrival_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea wire:model.live="notes" id="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror"></textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a wire:navigate href="{{ route('flights.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save"></i>
                        {{ $flight ? 'Update' : 'Create' }} Flight
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
