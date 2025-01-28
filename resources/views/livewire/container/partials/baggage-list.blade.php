<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>Tag Number</th>
                <th>Passenger</th>
                <th>Weight</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($container->baggage as $baggage)
                <tr>
                    <td>
                        <a wire:navigate href="{{ route('baggage.show', $baggage) }}" class="text-decoration-none">
                            {{ $baggage->tag_number }}
                        </a>
                    </td>
                    <td>
                        <a wire:navigate href="{{ route('passengers.show', $baggage->passenger) }}" class="text-decoration-none">
                            {{ $baggage->passenger->name }}
                        </a>
                    </td>
                    <td>{{ number_format($baggage->weight) }} kg</td>
                    <td>
                        <span class="badge bg-{{ $baggage->status === 'loaded' ? 'success' : 'warning' }}">
                            {{ ucfirst($baggage->status) }}
                        </span>
                    </td>
                    <td>
                        <button wire:click="removeBaggage({{ $baggage->id }})" class="btn btn-sm btn-danger">
                            <i class="bi bi-x"></i> Offload Baggage
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No baggage items in this container</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
