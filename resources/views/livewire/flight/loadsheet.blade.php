<div x-data="dragDropManager()">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Loadsheet</h3>
            @if ($loadsheet->status === 'released')
                <span class="badge bg-success">v{{ $loadsheet->version }}</span>
            @else
                <span class="badge bg-warning">Draft</span>
            @endif
            <div class="d-flex justify-content-between align-items-center">
                <div class="me-2">
                    <livewire:container.manager :flight="$flight" />
                </div>
                <button wire:click="releaseLoadsheet" class="btn btn-sm btn-primary"
                    {{ $loadsheet->status === 'released' ? 'disabled' : '' }}>
                    <i class="bi bi-check2-circle"></i> Release Loadsheet
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Aircraft Layout -->
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-body hold-body">
                            @php
                                $holdsByCode = $aircraft->type->holds->groupBy('code');
                            @endphp
                            <div class="hold-groups-container">
                                @foreach (['FH' => 'Forward Hold', 'AH' => 'Aft Hold', 'BH' => 'Bulk Hold'] as $code => $name)
                                    @if ($holdsByCode->has($code))
                                        <div class="hold-group" data-hold="{{ $code }}">
                                            <div class="hold-header">
                                                <h6>{{ $name }} ({{ $holdsByCode[$code]->first()->max_weight }} kg)</h6>
                                            </div>
                                            <div class="hold-positions">
                                                @php
                                                    $positions = $holdsByCode[$code]
                                                        ->first()
                                                        ->positions()
                                                        ->orderBy('row')
                                                        ->get()
                                                        ->groupBy('row');
                                                @endphp

                                                @foreach ($positions as $row => $rowPositions)
                                                    <div wire:key="hold-{{ $code }}-{{ $row }}" class="position-row">
                                                        <div class="row-number">{{ $row }}</div>
                                                        <div class="position-slots">
                                                            @if ($leftPosition = $rowPositions->firstWhere('side', 'L'))
                                                                <x-hold-position
                                                                    :position="$leftPosition"
                                                                    :containers="$containers"
                                                                    :container-positions="$containerPositions" />
                                                            @endif

                                                            @if ($rightPosition = $rowPositions->firstWhere('side', 'R'))
                                                                <x-hold-position
                                                                    :position="$rightPosition"
                                                                    :containers="$containers"
                                                                    :container-positions="$containerPositions" />
                                                            @endif

                                                            @if ($centerPosition = $rowPositions->firstWhere('side', null))
                                                                <x-hold-position
                                                                    :position="$centerPosition"
                                                                    :containers="$containers"
                                                                    :container-positions="$containerPositions" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Container List -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title text-center">Unplanned</h5>
                        </div>
                        <div class="card-body container-list-body">
                            <div class="container-list"
                                x-on:dragenter="dragEnter($event)"
                                x-on:dragover.prevent
                                x-on:dragleave="dragLeave($event)"
                                x-on:drop="dropContainer(null)">
                                @forelse ($availableContainers as $container)
                                    <div wire:key="container-{{ $container->id }}" class="draggable-container position-card"
                                        draggable="true"
                                        x-on:dragstart="startDrag($event, {{ $container->id }}, null)"
                                        x-on:dragend="endDrag"
                                        data-container-id="{{ $container->id }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card" style="cursor: move;">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-0">{{ $container->container_number }}</h6>
                                                                <small class="text-muted">{{ $container->type }}</small>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="fw-bold">{{ number_format($container->weight) }} kg</div>
                                                                <small class="text-muted">{{ $container->items_count ?? 0 }} pcs</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center">
                                        <p>No Unplanned Containers</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title text-center">Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Total Weight</h6>
                                    <p>{{ number_format($aircraft->max_weight) }} kg</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dragDropManager() {
            return {
                draggedContainer: null,
                draggedFromPosition: null,
                isDraggingContainer: false,

                startDrag(event, containerId, position) {
                    this.draggedContainer = containerId;
                    this.draggedFromPosition = position;
                    this.isDraggingContainer = true;

                    event.target.classList.add('dragging');
                    event.dataTransfer.setData('text/plain', '');
                    event.dataTransfer.effectAllowed = 'move';
                },

                endDrag(event) {
                    this.isDraggingContainer = false;
                    event.target.classList.remove('dragging');
                },

                dragEnter(event) {
                    const holdPosition = event.target.closest('.hold-position');
                    const containerList = event.target.closest('.container-list');

                    if (this.isDraggingContainer) {
                        if (
                            (holdPosition && !holdPosition.querySelector('.position-card')) ||
                            (holdPosition && holdPosition.closest('[data-hold="BH"]')) ||
                            containerList
                        ) {
                            if (holdPosition) {
                                holdPosition.classList.add('dragover');
                            } else if (containerList) {
                                containerList.classList.add('dragover');
                            }
                        }
                    }
                },

                dragLeave(event) {
                    const holdPosition = event.target.closest('.hold-position');
                    const containerList = event.target.closest('.container-list');

                    if (holdPosition) {
                        holdPosition.classList.remove('dragover');
                    }
                    if (containerList) {
                        containerList.classList.remove('dragover');
                    }
                },

                dropContainer(newPosition) {
                    if (this.draggedContainer) {
                        @this.updateContainerPosition(
                            this.draggedContainer,
                            this.draggedFromPosition,
                            newPosition
                        );

                        const holdPosition = event.target.closest('.hold-position');
                        const containerList = event.target.closest('.container-list');

                        if (holdPosition) {
                            holdPosition.classList.remove('dragover');
                        }
                        if (containerList) {
                            containerList.classList.remove('dragover');
                        }
                    }
                    this.draggedContainer = null;
                    this.draggedFromPosition = null;
                    this.isDraggingContainer = false;
                }
            };
        }
    </script>
</div>
