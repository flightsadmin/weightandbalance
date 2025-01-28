<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title m-0">{{ $airline->name }} Aircraft</h2>
                <p class="text-muted small m-0">Manage airline fleet and aircraft details</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search aircraft...">

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <a wire:navigate href="{{ route('aircraft.create', ['airline_id' => $airline->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Aircraft
                </a>
                <a wire:navigate href="{{ route('airlines.show', $airline) }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Airline
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Type</th>
                            <th>Model</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aircraft as $plane)
                            <tr>
                                <td>
                                    <a wire:navigate href="{{ route('aircraft.show', $plane) }}" class="text-decoration-none">
                                        {{ $plane->registration_number }}
                                    </a>
                                </td>
                                <td>{{ $plane->type->code }}</td>
                                <td>{{ $plane->type->name }}</td>
                                <td>{{ $plane->type->max_passengers }}</td>
                                <td>
                                    <span class="badge bg-{{ $plane->active ? 'success' : 'warning' }}">
                                        {{ $plane->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('aircraft.edit', $plane) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No aircraft found</td>
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
</div>
