<div>
    <div class="card" x-data="{
        holds: @js($holds),
        containers: @js($containers),
        selectedContainer: null,
        localStorageKey: 'loadplan-' + @js($flight->id),
    
        init() {
            this.loadFromStorage();
    
            // Listen for Livewire events
            this.$wire.on('resetAlpineState', () => this.resetState());
        },
    
        loadFromStorage() {
            const saved = localStorage.getItem(this.localStorageKey);
            if (saved) {
                const state = JSON.parse(saved);
                this.containers = state.containers;
            }
        },
    
        get unplannedContainers() {
            return this.containers.filter(c => !c.position);
        },
    
        selectContainer(container) {
            // If the container is already selected, deselect it
            if (this.selectedContainer?.id === container.id) {
                this.selectedContainer = null;
                return;
            }
            this.selectedContainer = container;
        },
    
        handlePositionClick(position) {
            // If a container is already selected
            if (this.selectedContainer) {
                if (!this.canDropHere(position)) return;
    
                if (this.selectedContainer.type === 'PMC') {
                    const otherSide = this.getOtherSidePosition(position);
                    if (!otherSide) return;
    
                    this.selectedContainer.position = [position.id, otherSide.id];
                } else {
                    this.selectedContainer.position = [position.id];
                }
    
                this.saveState();
                this.selectedContainer = null;
                return;
            }
    
            // If clicking on an empty position
            if (!this.isPositionOccupied(position)) return;
    
            // Get the container in this position
            const container = this.getContainerInPosition(position);
            if (!container) return;
    
            this.selectedContainer = container;
        },
    
        handleDoubleClick(position) {
            const container = this.getContainerInPosition(position);
            if (!container) return;
    
            container.position = null;
            this.selectedContainer = null;
            this.saveState();
        },
    
        getVerticalAdjacentPosition(position) {
            const allPositions = this.holds.flatMap(h => h.positions);
            const index = allPositions.findIndex(p => p.id === position.id);
            return allPositions[index + 4] || null;
        },
    
        canDropHere(position) {
            if (!this.selectedContainer) return false;
    
            if (this.selectedContainer.type === 'PMC') {
                const adjacentPosition = this.getVerticalAdjacentPosition(position);
                return adjacentPosition &&
                    !this.isPositionOccupied(position) &&
                    !this.isPositionOccupied(adjacentPosition);
            }
    
            return !this.isPositionOccupied(position);
        },
    
        isPositionOccupied(position) {
            return this.containers.some(c => c.position?.includes(position.id));
        },
    
        getContainerInPosition(position) {
            return this.containers.find(c => c.position?.includes(position.id));
        },
    
        getHoldWeight(hold) {
            return this.containers
                .filter(c => c.position?.some(p =>
                    hold.positions.some(pos => pos.id === p)
                ))
                .reduce((sum, container) => sum + container.weight, 0);
        },
    
        isHoldOverweight(hold) {
            return this.getHoldWeight(hold) > hold.max_weight;
        },
    
        saveState() {
            localStorage.setItem(this.localStorageKey, JSON.stringify({
                containers: this.containers,
                holds: this.holds
            }));
        },
    
        resetState() {
            this.containers.forEach(c => c.position = null);
            this.selectedContainer = null;
            localStorage.removeItem(this.localStorageKey);
        },
    
        async saveToServer() {
            await this.$wire.saveLoadplan(this.containers);
            this.saveState();
        }
    }">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Load Plan</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary" @click="saveToServer">
                    <i class="bi bi-save"></i> Save Load Plan
                </button>
                <button class="btn btn-sm btn-secondary" @click="resetState(); $wire.resetLoadplan()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="container-wrapper">
                <!-- Add scrolling wrapper -->
                <div class="holds-wrapper-scroll">
                    <div class="holds-wrapper">
                        <template x-for="hold in holds" :key="hold.id">
                            <div class="hold-container">
                                <!-- Hold Header at top -->
                                <div class="hold-header">
                                    <span x-text="hold.name"></span>
                                    <span class="badge" :class="isHoldOverweight(hold) ? 'bg-danger' : 'bg-success'"
                                        x-text="getHoldWeight(hold) + 'kg'">
                                    </span>
                                </div>

                                <div class="cargo-row" :class="{ 'bulk': hold.name.includes('Bulk') }">
                                    <!-- Right Side Positions -->
                                    <div class="position-column right" x-show="!hold.name.includes('Bulk')">
                                        <template x-for="position in hold.positions.filter(p => p.designation.endsWith('R'))"
                                            :key="position.id">
                                            <div class="cargo-slot"
                                                :class="{
                                                    'occupied': isPositionOccupied(position),
                                                    'drop-target': canDropHere(position),
                                                    'selected': getContainerInPosition(position)?.id === selectedContainer?.id,
                                                    'pmc': getContainerInPosition(position)?.type === 'PMC',
                                                    'ake': getContainerInPosition(position)?.type === 'AKE',
                                                    'cargo': getContainerInPosition(position)?.type === 'cargo',
                                                    'baggage': getContainerInPosition(position)?.type === 'baggage'
                                                }"
                                                :data-side="position.designation"
                                                @click="handlePositionClick(position)"
                                                @dblclick="handleDoubleClick(position)">
                                                <template x-if="getContainerInPosition(position)">
                                                    <div>
                                                        <div x-text="getContainerInPosition(position).uld_code"></div>
                                                        <div class="unit-number small text-muted"
                                                            x-text="getContainerInPosition(position).pieces > 0 ? getContainerInPosition(position).type + ' (' + getContainerInPosition(position).pieces + 'pcs)' : 'Empty'">
                                                        </div>
                                                        <div>
                                                            <i class="bi"
                                                                :class="getContainerInPosition(position).type === 'baggage' ? 'bi-luggage' :
                                                                    'bi-box-seam'"></i>
                                                            <span x-text="getContainerInPosition(position).weight + 'kg'"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!getContainerInPosition(position)">
                                                    <span class="position-code" x-text="position.designation"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Bulk Positions (centered) -->
                                    <div class="position-column center" x-show="hold.name.includes('Bulk')">
                                        <template x-for="position in hold.positions" :key="position.id">
                                            <div class="cargo-slot"
                                                :class="{
                                                    'occupied': isPositionOccupied(position),
                                                    'drop-target': canDropHere(position),
                                                    'selected': getContainerInPosition(position)?.id === selectedContainer?.id,
                                                    'ake': getContainerInPosition(position)?.type === 'AKE',
                                                    'cargo': getContainerInPosition(position)?.type === 'cargo',
                                                    'baggage': getContainerInPosition(position)?.type === 'baggage'
                                                }"
                                                :data-side="position.designation"
                                                @click="handlePositionClick(position)"
                                                @dblclick="handleDoubleClick(position)">
                                                <template x-if="getContainerInPosition(position)">
                                                    <div>
                                                        <div x-text="getContainerInPosition(position).uld_code"></div>
                                                        <div class="unit-number small text-muted"
                                                            x-text="getContainerInPosition(position).pieces > 0 ? getContainerInPosition(position).type + ' (' + getContainerInPosition(position).pieces + 'pcs)' : 'Empty'">
                                                        </div>
                                                        <div>
                                                            <i class="bi"
                                                                :class="getContainerInPosition(position).type === 'baggage' ? 'bi-luggage' :
                                                                    'bi-box-seam'"></i>
                                                            <span x-text="getContainerInPosition(position).weight + 'kg'"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!getContainerInPosition(position)">
                                                    <span class="position-code" x-text="position.designation"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Left Side Positions -->
                                    <div class="position-column left" x-show="!hold.name.includes('Bulk')">
                                        <template x-for="position in hold.positions.filter(p => p.designation.endsWith('L'))"
                                            :key="position.id">
                                            <div class="cargo-slot"
                                                :class="{
                                                    'occupied': isPositionOccupied(position),
                                                    'drop-target': canDropHere(position),
                                                    'selected': getContainerInPosition(position)?.id === selectedContainer?.id,
                                                    'pmc': getContainerInPosition(position)?.type === 'PMC',
                                                    'ake': getContainerInPosition(position)?.type === 'AKE',
                                                    'cargo': getContainerInPosition(position)?.type === 'cargo',
                                                    'baggage': getContainerInPosition(position)?.type === 'baggage'
                                                }"
                                                :data-side="position.designation"
                                                @click="handlePositionClick(position)"
                                                @dblclick="handleDoubleClick(position)">
                                                <template x-if="getContainerInPosition(position)">
                                                    <div>
                                                        <div x-text="getContainerInPosition(position).uld_code"></div>
                                                        <div class="unit-number small text-muted"
                                                            x-text="getContainerInPosition(position).pieces > 0 ? getContainerInPosition(position).type + ' (' + getContainerInPosition(position).pieces + 'pcs)' : 'Empty'">
                                                        </div>
                                                        <div>
                                                            <i class="bi"
                                                                :class="getContainerInPosition(position).type === 'baggage' ? 'bi-luggage' :
                                                                    'bi-box-seam'"></i>
                                                            <span x-text="getContainerInPosition(position).weight + 'kg'"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!getContainerInPosition(position)">
                                                    <span class="position-code" x-text="position.designation"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Holding Area -->
                <div class="unplanned-area mt-2">
                    <template x-for="container in unplannedContainers" :key="container.id">
                        <div class="container border border-1 border-primary"
                            :class="{
                                'selected': selectedContainer?.id === container.id
                            }"
                            @click="selectContainer(container)">
                            <div x-text="container.uld_code"></div>
                            <div class="unit-number small text-muted"
                                x-text="container.pieces > 0 ? container.type + ' (' + container.pieces + 'pcs)' : 'Empty'">
                            </div>
                            <div>
                                <i class="bi" :class="container.type === 'baggage' ? 'bi-luggage' : 'bi-box-seam'"></i>
                                <span x-text="container.weight + 'kg'"></span>
                                <template x-if="container.type === 'PMC'" class="badge bg-secondary ms-1">PMC</template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <style>
        .container-wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .unplanned-area {
            border: 2px dashed #6c757d;
            background: #f8f9fa;
            padding: 10px;
            width: 100%;
            min-height: 200px;
            align-items: start;
            text-align: center;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 5px;
            margin-left: auto;
            margin-right: auto;
        }

        .holds-wrapper-scroll {
            width: 100%;
            overflow-x: auto;
            padding: 10px 0;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
        }

        .holds-wrapper-scroll::-webkit-scrollbar {
            height: 8px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 2px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 2px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        .holds-wrapper {
            display: inline-flex;
            gap: 5px;
            min-width: min-content;
        }

        .hold-container {
            border: 1px solid #0d6efd;
            border-radius: 2px;
            padding: 5px;
            background: #f8f9fa;
            flex-shrink: 0;
            margin: 0 1px;
            display: flex;
            flex-direction: column;
        }

        .hold-header {
            text-align: center;
            padding: 4px 0 8px;
            font-weight: bold;
            border-bottom: 1px solid #0d77e0;
            margin-bottom: 8px;
        }

        .cargo-row {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            position: relative;
        }

        .cargo-row.bulk {
            justify-content: center;
        }

        .position-column {
            display: flex;
            gap: 3px;
            justify-content: center;
        }

        .cargo-slot {
            border: 1px solid #dee2e6;
            border-radius: 2px;
            padding: 4px;
            text-align: center;
            background: white;
            width: 80px;
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: 0.2s;
            cursor: pointer;
            position: relative;
            font-size: 11px;
        }

        .unit-number {
            font-size: 9px;
            opacity: 0.7;
            margin-bottom: 2px;
        }

        .position-code {
            font-size: 10px;
            color: #6c757d;
        }

        .cargo-slot.occupied.cargo {
            background-color: #fff3cd;
            border: 1px dashed #fd7e14;
            color: #212529;
        }

        .cargo-slot.occupied.baggage {
            background-color: #cfe2ff;
            border: 1px dashed #0d6efd;
            color: #212529;
        }

        .cargo-slot.occupied.pmc {
            position: absolute;
            left: 0;
            right: 0;
            height: 164px;
            background-color: #fff3cd;
            border: 1px dashed #fd7e14;
            z-index: 1;
        }

        .cargo-slot.drop-target.pmc {
            position: absolute;
            left: 0;
            right: 0;
            height: 164px;
            border: 1px dashed #198754;
            z-index: 1;
        }

        .cargo-slot.occupied.ake {
            grid-column: span 1;
            background-color: #e2e3e5;
            border: 1px solid #6c757d;
        }

        .cargo-slot::before {
            content: attr(data-side);
            position: absolute;
            top: 2px;
            left: 2px;
            font-size: 9px;
            color: #6c757d;
        }

        .unplanned-area .container {
            width: auto;
            padding-top: 2px;
            padding-bottom: 2px;
            border-radius: 5px;
            font-size: 10px;
        }

        .cargo-slot:hover,
        .container:hover {
            transform: scale(1.05);
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
        }

        .occupied {
            background-color: #ffc107;
            color: #212529;
        }

        .selected {
            border: 1px solid #0d6efd !important;
            background-color: #e7f1ff;
        }

        .drop-target {
            background-color: #d4edda !important;
            border: 1px dashed #198754 !important;
        }
    </style>
</div>
