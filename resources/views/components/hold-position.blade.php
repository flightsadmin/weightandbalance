<div class="hold-position border {{ $position->side ? 'position-' . $position->side : 'position-center' }}"
    x-on:dragenter="dragEnter($event)"
    x-on:dragover.prevent
    x-on:dragleave="dragLeave($event)"
    x-on:drop="dropContainer('{{ $position->id }}')"
    data-position="{{ $position->id }}">

    @if ($position->hold->code === 'BH')
        {{-- Bulk Hold Display --}}
        <div class="bulk-containers-list">
            @php
                $positionId = $position->id;
                $bulkContainers = $containers->filter(function ($container) use ($containerPositions, $positionId) {
                    return isset($containerPositions[$container->id]) && $containerPositions[$container->id] == $positionId;
                });
            @endphp

            @if ($bulkContainers->isNotEmpty())
                @foreach ($bulkContainers as $container)
                    <div class="bulk-container-item draggable-container"
                        draggable="true"
                        x-on:dragstart="startDrag($event, {{ $container->id }}, '{{ $position->id }}')"
                        x-on:dragend="endDrag"
                        data-container-id="{{ $container->id }}">
                        <div class="bulk-container-details">
                            <span class="container-number">{{ $container->container_number }}</span>
                            <span class="container-weight">{{ number_format($container->weight) }} kg</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-bulk-position">
                    <div class="position-code">{{ $position->code }}</div>
                    <i class="bi bi-box-seam"></i>
                    <small class="text-muted">{{ $position->max_weight }} kg</small>
                </div>
            @endif
        </div>
    @else
        {{-- Regular Hold Position Display --}}
        @if ($container = $containers->firstWhere('id', array_search($position->id, $containerPositions)))
            <div class="position-card draggable-container"
                style="cursor: move;"
                draggable="true"
                x-on:dragstart="startDrag($event, {{ $container->id }}, '{{ $position->id }}')"
                x-on:dragend="endDrag"
                data-container-id="{{ $container->id }}">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="mb-1">{{ $container->container_number }}</h6>
                        <div class="fw-bold">{{ number_format($container->weight) }} kg</div>
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
