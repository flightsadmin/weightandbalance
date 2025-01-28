<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Crew</h2>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search crew...">

                <select wire:model.live="position" class="form-select form-select-sm">
                    <option value="">All Positions</option>
                    <option value="captain">Captain</option>
                    <option value="first_officer">First Officer</option>
                    <option value="flight_attendant">Flight Attendant</option>
                </select>
            </div>
            <div>
                <a wire:navigate href="{{ route('crews.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Crew Member
                </a>
                <a wire:navigate href="{{ route('flights.show', $flight) }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Flight
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Employee ID</th>
                            <th>Flight</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($crews as $crew)
                            <tr>
                                <td>
                                    <a href="{{ route('crews.show', $crew) }}" class="text-decoration-none">
                                        {{ $crew->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('crews.show', $crew) }}" class="text-decoration-none">
                                        {{ $crew->employee_id }}
                                    </a>
                                </td>
                                <td>
                                    @if ($crew->flights->isNotEmpty())
                                        <a href="{{ route('flights.show', $crew->flights->first()) }}" class="text-decoration-none">
                                            {{ $crew->flights->first()->flight_number }}
                                        </a>
                                        <span class="small text-muted">
                                            ({{ $crew->flights->first()->departure_airport }} →
                                            {{ $crew->flights->first()->arrival_airport }})
                                        </span>
                                    @else
                                        <span class="text-muted">No active flight</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $crew->position === 'captain' ? 'primary' : ($crew->position === 'first_officer' ? 'success' : 'warning') }}">
                                        {{ ucwords(str_replace('_', ' ', $crew->position)) }}
                                    </span>
                                </td>
                                <td>
                                    <a wire:navigate href="{{ route('crews.edit', $crew) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No crew members found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $crews->links() }}
            </div>
        </div>
    </div>
</div>
