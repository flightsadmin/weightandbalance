<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Airlines</h2>
            <div class="d-flex align-items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm w-auto"
                    placeholder="Search airlines...">
                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <a href="{{ route('airlines.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Add Airline
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Name
                                @if ($sortField === 'name')
                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>IATA Code</th>
                            <th>ICAO Code</th>
                            <th>Country</th>
                            <th>Aircraft</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($airlines as $airline)
                            <tr wire:key="airline-{{ $airline->id }}">
                                <td>
                                    <a href="{{ route('airlines.show', $airline) }}" class="text-decoration-none">
                                        {{ $airline->name }}
                                    </a>
                                </td>
                                <td>{{ $airline->iata_code }}</td>
                                <td>{{ $airline->icao_code }}</td>
                                <td>{{ $airline->country }}</td>
                                <td>{{ $airline->aircraft->count() }}</td>
                                <td>
                                    <button wire:click="toggleStatus({{ $airline->id }})"
                                        class="btn btn-sm btn-{{ $airline->active ? 'success' : 'danger' }}">
                                        <i class="bi bi-{{ $airline->active ? 'check' : 'x' }}"></i>
                                        {{ $airline->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td>
                                    <a href="{{ route('airlines.show', $airline) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('airlines.edit', $airline) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No airlines found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $airlines->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('alert', (data) => {
                toastr[data.type](data.message);
            });
        });
    </script>
</div>
