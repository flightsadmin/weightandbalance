<div class="hold-position border {{ $position->side ? 'position-' . $position->side : 'position-center' }}"
    x-on:click="selectPosition('{{ $position->id }}')"
    :class="{ 'selected': selectedPosition === '{{ $position->id }}', 'occupied': {{ isset($containerPositions[$position->id]) ? 'true' : 'false' }} }"
    data-position="{{ $position->id }}">

    @if ($position->hold->code === 'BH')
        <div class="bulk-containers-list">
            @php
                $positionId = $position->id;
                $bulkContainers = $containers->filter(function ($container) use ($containerPositions, $positionId) {
                    return isset($containerPositions[$container->id]) && $containerPositions[$container->id] == $positionId;
                });
            @endphp

            @if ($bulkContainers->isNotEmpty())
                @foreach ($bulkContainers as $container)
                    <div class="bulk-container-item"
                        x-on:click.stop="selectContainer({{ $container->id }})"
                        :class="{ 'selected': selectedContainer === {{ $container->id }} }"
                        data-container-id="{{ $container->id }}">
                        <div class="bulk-container-details">
                            <span class="container-number">{{ $container->container_number }}</span>
                            <span class="container-weight">{{ number_format($container->weight) }} kg</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-bulk-position">
                    <div class="position-code">{{ $position->position }}</div>
                    <i class="bi bi-box-seam"></i>
                    <small class="text-muted">{{ $position->max_weight }} kg</small>
                </div>
            @endif
        </div>
    @else
        {{-- Regular Hold Position Display --}}
        @php
            $containerId = array_search($position->id, $containerPositions);
            $container = $containerId ? $containers->firstWhere('id', $containerId) : null;
        @endphp

        @if ($container)
            <div class="position-card"
                x-on:click.stop="selectContainer({{ $container->id }})"
                :class="{ 'selected': selectedContainer === {{ $container->id }} }">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="mb-1">{{ $container->container_number }}</h6>
                        <small class="fw-bold">{{ number_format($container->weight) }} kg</small>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-position">
                <div>{{ $position->code }}</div>
                <i class="bi bi-box-seam"></i>
                <small class="text-muted">{{ $position->max_weight }} kg</small>
            </div>
        @endif
    @endif
</div>
