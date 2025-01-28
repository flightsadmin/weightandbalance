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
                    <option value="cabin_crew">Cabin Crew</option>
                </select>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($crews as $crew)
                            <tr wire:key="crew-{{ $crew->id }}">
                                <td>
                                    {{ $crew->name }}
                                </td>
                                <td>
                                    {{ $crew->employee_id }}
                                </td>
                                <td>
                                    @if ($crew->flights->isNotEmpty())
                                        {{ $crew->flights->first()->flight_number }}

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
