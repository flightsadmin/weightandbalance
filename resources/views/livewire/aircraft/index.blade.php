<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Aircraft</h2>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search aircraft...">

                <select wire:model.live="airline_id" class="form-select form-select-sm">
                    <option value="">All Airlines</option>
                    @foreach ($airlines as $airline)
                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <a href="{{ route('aircraft.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Add Aircraft
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Airline</th>
                            <th>Type</th>
                            <th>Model</th>
                            <th>Capacity</th>
                            <th>Active Flights</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aircraft as $plane)
                            <tr wire:key="aircraft-{{ $plane->id }}">
                                <td>
                                    <a href="{{ route('aircraft.show', $plane) }}" class="text-decoration-none">
                                        {{ $plane->registration_number }}
                                    </a>
                                </td>
                                <td>
                                    @if ($plane->airline)
                                        <a href="{{ route('airlines.show', $plane->airline) }}" class="text-decoration-none">
                                            {{ $plane->airline->iata_code }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td> {{ $plane->type->code }}</td>
                                <td>{{ $plane->type->name }} </td>
                                <td>{{ $plane->type->max_passengers }} pax / {{ number_format($plane->type->cargo_capacity) }} kg</td>
                                <td>{{ $plane->flights->count() }}</td>
                                <td>
                                    <button wire:click="toggleStatus({{ $plane->id }})"
                                        class="btn btn-sm btn-{{ $plane->active ? 'success' : 'danger' }}">
                                        <i class="bi bi-{{ $plane->active ? 'check' : 'x' }}"></i>
                                        {{ $plane->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <a href="{{ route('aircraft.show', $plane) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('aircraft.edit', $plane) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No aircraft found.</td>
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
