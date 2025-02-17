<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title m-0">Boarding Control</h2>
        <div class="d-flex justify-content-between align-items-center gap-2">
            <button class="btn btn-sm btn-primary">
                Boarded: {{ $boardedCount }}/{{ $totalCount }}
            </button>
        </div>
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button class="nav-link {{ $activeTab === 'seat' ? 'active' : '' }}" wire:click="setTab('seat')">Board by Seat</button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setTab('list')">Passenger List</button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $activeTab === 'boarded' ? 'active' : '' }}" wire:click="setTab('boarded')">Boarded
                    Passengers</button>
            </li>
        </ul>

        @if ($activeTab === 'seat')
            <div class="row">
                <div class="col-md-6">
                    <form wire:submit="boardBySeat">
                        <div class="input-group">
                            <input type="text" wire:model="seatNumber"
                                class="form-control form-control-sm"
                                placeholder="Enter seat number..."
                                autofocus>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-person-check"></i> Board
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($activeTab === 'list')
            <div class="mb-3">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control form-control-sm"
                            placeholder="Search passengers...">
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary btn-sm"
                            wire:click="boardSelected"
                            @if (empty($selectedPassengers)) disabled @endif>
                            Board Selected ({{ count($selectedPassengers) }})
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        @if ($activeTab === 'list')
                            <th>
                                <input type="checkbox" wire:model.live="selectAll">
                            </th>
                        @endif
                        <th>Seat</th>
                        <th>Name</th>
                        <th>Ticket Number</th>
                        <th>Type</th>
                        @if ($activeTab === 'boarded')
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($passengers as $passenger)
                        <tr>
                            @if ($activeTab === 'list')
                                <td>
                                    <input type="checkbox"
                                        wire:model.live="selectedPassengers"
                                        value="{{ $passenger->id }}">
                                </td>
                            @endif
                            <td>{{ $passenger->seat?->designation ?? 'No Seat' }}</td>
                            <td>{{ $passenger->name }}</td>
                            <td>{{ $passenger->ticket_number }}</td>
                            <td>{{ ucfirst($passenger->type) }}</td>
                            @if ($activeTab === 'boarded')
                                <td>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="unboardPassenger({{ $passenger->id }})"
                                        wire:confirm="Are you sure you want to unboard this passenger?">
                                        <i class="bi bi-person-x"></i> Unboard
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No passengers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $passengers->links() }}
        </div>
    </div>
</div>
