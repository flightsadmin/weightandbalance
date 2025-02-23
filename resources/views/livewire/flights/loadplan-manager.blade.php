<div x-data="{
    selectedContainer: null,
    selectedPosition: null,

    selectContainer(containerId) {
        if (this.selectedContainer === containerId) {
            this.selectedContainer = null;
        } else {
            this.selectedContainer = containerId;
            this.selectedPosition = null;
        }
    },

    selectPosition(positionId) {
        if (this.selectedContainer) {
            $wire.updateContainerPosition(this.selectedContainer, null, positionId);
            this.selectedContainer = null;
            this.selectedPosition = null;
        } else if (this.selectedPosition === positionId) {
            this.selectedPosition = null;
        } else {
            let containerInPosition = Object.entries($wire.containerPositions)
                .find(([contId, posId]) => posId === positionId);

            if (containerInPosition) {
                $wire.updateContainerPosition(containerInPosition[0], positionId, null);
            }

            this.selectedPosition = positionId;
            this.selectedContainer = null;
        }
    },

    removeSelectedContainer() {
        // Handle container in position
        if (this.selectedPosition) {
            let containerInPosition = Object.entries($wire.containerPositions)
                .find(([contId, posId]) => posId === this.selectedPosition);

            if (containerInPosition) {
                $wire.updateContainerPosition(containerInPosition[0], this.selectedPosition, null);
            }
            this.selectedPosition = null;
        }
        // Handle selected container
        else if (this.selectedContainer) {
            let currentPosition = $wire.containerPositions[this.selectedContainer];
            if (currentPosition) {
                $wire.updateContainerPosition(this.selectedContainer, currentPosition, null);
            }
            this.selectedContainer = null;
        }
    }
}">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Loadplan</h3>
            <div class="d-flex gap-2">
                <button wire:click="releaseLoadplan" class="btn btn-sm btn-{{ $loadplan->status === 'released' ? 'warning' : 'primary' }}">
                    <i class="bi bi-{{ $loadplan->status === 'released' ? 'arrow-repeat' : 'check2-circle' }}"></i>
                    Release Loadplan v{{ $loadplan->version + 1 }}
                </button>
                <button class="btn btn-sm btn-primary" wire:click="previewLIRF"
                    @if ($loadplan->status !== 'released') disabled @endif data-bs-toggle="modal"
                    data-bs-target="#lirfPreviewModal">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Preview LIRF
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
                                                            @php
                                                                $rowPositions = collect($rowPositions); // Convert to collection
                                                            @endphp

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
                            <h5 class="card-title">Unplanned Containers</h5>
                        </div>
                        <div class="card-body"
                            x-on:click="removeSelectedContainer()"
                            :class="{ 'unplanned-area': true, 'highlight': selectedContainer || selectedPosition }">
                            <div class="container-list row g-2" x-on:click.stop>
                                @forelse ($availableContainers as $container)
                                    <div class="container-item col-md-4 {{ str($container->pivot->type)->lower() }}"
                                        x-on:click.stop="selectContainer({{ $container->id }})"
                                        :class="{ 'selected': selectedContainer === {{ $container->id }} }">
                                        <div class="card container-card">
                                            <div class="card-body">
                                                <div class="fw-bold">{{ $container->container_number }}</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span
                                                        class="badge bg-{{ $container->pivot->type === 'baggage' ? 'primary' : 'warning' }}">
                                                        {{ ucfirst($container->pivot->type) }}
                                                        <i
                                                            class="bi bi-{{ $container->pivot->type === 'baggage' ? 'luggage' : 'box' }}"></i>
                                                    </span>
                                                    <div class="fw-bold">{{ number_format($container->pivot->weight) }}kg</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted">
                                        <i class="bi bi-inbox display-6"></i>
                                        <p>No unplanned containers</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <livewire:container.manager :flight="$flight" />
                </div>
            </div>
        </div>
    </div>

    <!-- LIRF Preview Modal -->
    <div class="modal modal-fullscreen fade" id="lirfPreviewModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Loading Instruction Report Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="lirfPrintArea">
                    @if ($showLirfPreview)
                        @include('livewire.flights.loading-instruction')
                    @endif
                </div>
                <div class="modal-footer py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="generatePDF()">
                        <i class="bi bi-printer"></i> Print LIRF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        async function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            const element = document.getElementById("lirfPrintArea");

            const canvas = await html2canvas(element, {
                scale: 2
            });
            const imgData = canvas.toDataURL("image/png");

            const imgWidth = 190;
            const pageHeight = 297;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;

            let position = 10;
            doc.addImage(imgData, "PNG", 10, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                doc.addPage();
                doc.addImage(imgData, "PNG", 10, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            doc.save("Loading Instruction Report - {{ $flight->flight_number }}.pdf");
        }
    </script>
</div>
