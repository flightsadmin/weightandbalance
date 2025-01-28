<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>AWB Number</th>
                <th>Description</th>
                <th>Weight</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($container->cargo as $cargo)
                <tr>
                    <td>
                        <a wire:navigate href="{{ route('cargo.show', $cargo) }}" class="text-decoration-none">
                            {{ $cargo->awb_number }}
                        </a>
                    </td>
                    <td>{{ Str::limit($cargo->description, 50) }}</td>
                    <td>{{ number_format($cargo->weight) }} kg</td>
                    <td>
                        <span class="badge bg-{{ $cargo->status === 'loaded' ? 'success' : 'warning' }}">
                            {{ ucfirst($cargo->status) }}
                        </span>
                    </td>
                    <td>
                        <button wire:click="removeCargo({{ $cargo->id }})" class="btn btn-sm btn-danger">
                            <i class="bi bi-x"></i> Offload Cargo
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No cargo items in this container</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
