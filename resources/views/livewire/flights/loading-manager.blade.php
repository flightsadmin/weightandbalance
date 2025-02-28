<div>
    <div class="card" x-data="{
        holds: @js($holds),
        containers: @js($containers),
        selectedContainer: null,
        localStorageKey: 'loadplan-' + @js($flight->id),
        showWeightSummary: false,
    
        init() {
            this.loadFromStorage();
            this.$wire.on('resetAlpineState', () => this.resetState());
            this.calculateWeights();
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
    
        get totalWeight() {
            return this.containers.reduce((sum, c) => sum + (c.weight || 0), 0);
        },
    
        selectContainer(container) {
            if (this.selectedContainer?.id === container.id) {
                this.selectedContainer = null;
                return;
            }
            this.selectedContainer = container;
        },
    
        handlePositionClick(position) {
            if (this.selectedContainer) {
                if (!this.canDropHere(position)) {
                    this.$dispatch('notify', {
                        type: 'error',
                        message: 'Invalid position for this container type'
                    });
                    return;
                }
    
                if (this.selectedContainer.type === 'PMC') {
                    const otherSide = this.getOtherSidePosition(position);
                    if (!otherSide) {
                        this.$dispatch('notify', {
                            type: 'error',
                            message: 'PMC requires two adjacent positions'
                        });
                        return;
                    }
                    this.selectedContainer.position = [position.id, otherSide.id];
                } else {
                    this.selectedContainer.position = [position.id];
                }
    
                this.saveState();
                this.selectedContainer = null;
                this.calculateWeights();
                return;
            }
    
            if (!this.isPositionOccupied(position)) return;
    
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
            this.calculateWeights();
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
                .reduce((sum, container) => sum + (container.weight || 0), 0);
        },
    
        isHoldOverweight(hold) {
            return this.getHoldWeight(hold) > hold.max_weight;
        },
    
        getHoldUtilization(hold) {
            const weight = this.getHoldWeight(hold);
            return (weight / hold.max_weight) * 100;
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
            this.calculateWeights();
        },
    
        async saveToServer() {
            await this.$wire.saveLoadplan(this.containers);
            this.saveState();
        },
    
        calculateWeights() {
            this.holds.forEach(hold => {
                hold.currentWeight = this.getHoldWeight(hold);
                hold.utilization = this.getHoldUtilization(hold);
            });
        }
    }">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <h5 class="card-title m-0">Load Plan</h5>
                <span class="badge" :class="totalWeight > 0 ? 'bg-primary' : 'bg-secondary'">
                    Total: <span x-text="totalWeight"></span>kg
                </span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" @click="showWeightSummary = !showWeightSummary">
                    <i class="bi bi-clipboard-data"></i> Weight Summary
                </button>
                <button class="btn btn-sm btn-primary" @click="saveToServer">
                    <i class="bi bi-save"></i> Save Load Plan
                </button>
                <button class="btn btn-sm btn-danger" @click="resetState(); $wire.resetLoadplan()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="container-wrapper">
                <!-- Weight Summary Modal -->
                <div class="modal fade" :class="{ 'show': showWeightSummary }" x-show="showWeightSummary"
                    tabindex="-1" style="display: none;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Weight Summary</h5>
                                <button type="button" class="btn-close" @click="showWeightSummary = false"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Hold</th>
                                                <th>Current</th>
                                                <th>Maximum</th>
                                                <th>Utilization</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="hold in holds" :key="hold.id">
                                                <tr>
                                                    <td x-text="hold.name"></td>
                                                    <td>
                                                        <span x-text="getHoldWeight(hold)"></span>kg
                                                    </td>
                                                    <td>
                                                        <span x-text="hold.max_weight"></span>kg
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 15px;">
                                                            <div class="progress-bar"
                                                                :class="{
                                                                    'bg-success': getHoldUtilization(hold) < 80,
                                                                    'bg-warning': getHoldUtilization(hold) >= 80 && getHoldUtilization(
                                                                        hold) < 95,
                                                                    'bg-danger': getHoldUtilization(hold) >= 95
                                                                }"
                                                                :style="'width: ' + getHoldUtilization(hold) + '%'"
                                                                x-text="Math.round(getHoldUtilization(hold)) + '%'">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Holds Layout -->
                <div class="card">
                    <div class="card-body">
                        <div class="holds-wrapper-scroll">
                            <div class="holds-wrapper">
                                <template x-for="hold in holds" :key="hold.id">
                                    <div class="hold-container" :class="{ 'bulk': hold.name.includes('Bulk') }">
                                        <div class="hold-header">
                                            <div class="d-flex justify-content-between align-items-center px-2">
                                                <span x-text="hold.name"></span>
                                                <div class="weight-badge" :class="{ 'text-danger': isHoldOverweight(hold) }">
                                                    <span x-text="getHoldWeight(hold)"></span>/<span x-text="hold.max_weight"></span>kg
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar"
                                                    :class="{
                                                        'bg-success': getHoldUtilization(hold) < 80,
                                                        'bg-warning': getHoldUtilization(hold) >= 80 && getHoldUtilization(hold) < 95,
                                                        'bg-danger': getHoldUtilization(hold) >= 95
                                                    }"
                                                    :style="'width: ' + getHoldUtilization(hold) + '%'">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cargo-row" :class="{ 'bulk': hold.name.includes('Bulk') }">
                                            <!-- Left Side Positions -->
                                            <div class="position-column left" x-show="!hold.name.includes('Bulk')">
                                                <template x-for="position in hold.positions.filter(p => p.designation.endsWith('L'))"
                                                    :key="position.id">
                                                    <div class="cargo-slot"
                                                        :class="{
                                                            'occupied': isPositionOccupied(position),
                                                            'drop-target': selectedContainer && canDropHere(position),
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
                                                            <div class="container-info">
                                                                <span class="position-number" x-text="position.designation"></span>
                                                                <div class="container-id"
                                                                    x-text="getContainerInPosition(position).uld_code">
                                                                </div>
                                                                <div class="container-type"
                                                                    x-text="getContainerInPosition(position).pieces > 0 ? 
                                                                getContainerInPosition(position).type + ' (' + getContainerInPosition(position).pieces + 'pcs)' : 
                                                                'Empty'">
                                                                </div>
                                                                <div class="container-weight">
                                                                    <i class="bi"
                                                                        :class="getContainerInPosition(position).type === 'baggage' ?
                                                                            'bi-luggage' :
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

                                            <!-- Right Side Positions -->
                                            <div class="position-column right" x-show="!hold.name.includes('Bulk')">
                                                <template x-for="position in hold.positions.filter(p => p.designation.endsWith('R'))"
                                                    :key="position.id">
                                                    <div class="cargo-slot"
                                                        :class="{
                                                            'occupied': isPositionOccupied(position),
                                                            'drop-target': selectedContainer && canDropHere(position),
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
                                                            <div class="container-info">
                                                                <span class="position-number" x-text="position.designation"></span>
                                                                <div class="container-id"
                                                                    x-text="getContainerInPosition(position).uld_code">
                                                                </div>
                                                                <div class="container-type"
                                                                    x-text="getContainerInPosition(position).pieces > 0 ? 
                                                                getContainerInPosition(position).type + ' (' + getContainerInPosition(position).pieces + 'pcs)' : 
                                                                'Empty'">
                                                                </div>
                                                                <div class="container-weight">
                                                                    <i class="bi"
                                                                        :class="getContainerInPosition(position).type === 'baggage' ?
                                                                            'bi-luggage' :
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

                                            <!-- Bulk Positions -->
                                            <div class="position-column center" x-show="hold.name.includes('Bulk')">
                                                <template x-for="position in hold.positions" :key="position.id">
                                                    <div class="cargo-slot"
                                                        :class="{
                                                            'occupied': isPositionOccupied(position),
                                                            'drop-target': selectedContainer && canDropHere(position),
                                                            'selected': getContainerInPosition(position)?.id === selectedContainer?.id,
                                                            'ake': getContainerInPosition(position)?.type === 'AKE',
                                                            'cargo': getContainerInPosition(position)?.type === 'cargo',
                                                            'baggage': getContainerInPosition(position)?.type === 'baggage'
                                                        }"
                                                        :data-side="position.designation"
                                                        @click="handlePositionClick(position)"
                                                        @dblclick="handleDoubleClick(position)">
                                                        <template x-if="getContainerInPosition(position)">
                                                            <div class="container-info">
                                                                <span class="position-number" x-text="position.designation"></span>
                                                                <div class="container-id"
                                                                    x-text="getContainerInPosition(position).uld_code">
                                                                </div>
                                                                <div class="container-type"
                                                                    x-text="getContainerInPosition(position).pieces > 0 ? 
                                                                getContainerInPosition(position).type + ' (' + getContainerInPosition(position).pieces + 'pcs)' : 
                                                                'Empty'">
                                                                </div>
                                                                <div class="container-weight">
                                                                    <i class="bi"
                                                                        :class="getContainerInPosition(position).type === 'baggage' ?
                                                                            'bi-luggage' :
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
                    </div>
                </div>

                <!-- Unplanned Items -->
                <div class="unplanned-section mt-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <h6 class="card-title m-0">Available ULDs</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="unplanned-area">
                                        <template x-for="container in unplannedContainers" :key="container.id">
                                            <div class="container"
                                                :class="{
                                                    'selected': selectedContainer?.id === container.id,
                                                    'pmc-container': container.type === 'PMC',
                                                    'ake-container': container.type === 'AKE',
                                                    'baggage-container': container.type === 'baggage',
                                                    'cargo-container': container.type === 'cargo'
                                                }"
                                                @click="selectContainer(container)">
                                                <div class="container-info">
                                                    <div class="container-id" x-text="container.uld_code"></div>
                                                    <div class="container-type"
                                                        x-text="container.pieces > 0 ? 
                                                            container.type + ' (' + container.pieces + 'pcs)' : 
                                                            'Empty'">
                                                    </div>
                                                    <div class="container-weight">
                                                        <i class="bi"
                                                            :class="container.type === 'baggage' ? 'bi-luggage' : 'bi-box-seam'"></i>
                                                        <span x-text="container.weight + 'kg'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <h6 class="card-title m-0">Hold Summary</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Hold</th>
                                                    <th class="text-end">Weight</th>
                                                    <th class="text-end">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="hold in holds" :key="hold.id">
                                                    <tr>
                                                        <td x-text="hold.name"></td>
                                                        <td class="text-end">
                                                            <span x-text="getHoldWeight(hold)"></span>/<span
                                                                x-text="hold.max_weight"></span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span
                                                                :class="{
                                                                    'text-success': getHoldUtilization(hold) < 80,
                                                                    'text-warning': getHoldUtilization(hold) >= 80 && getHoldUtilization(
                                                                        hold) < 95,
                                                                    'text-danger': getHoldUtilization(hold) >= 95
                                                                }"
                                                                x-text="Math.round(getHoldUtilization(hold)) + '%'"></span>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

        .holds-wrapper-scroll {
            width: 100%;
            overflow-x: auto;
            padding: 10px 0;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
            display: flex;
            align-items: flex-start;
        }

        .holds-wrapper-scroll::-webkit-scrollbar {
            height: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 4px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        .holds-wrapper {
            display: flex;
            gap: 10px;
            padding: 0 10px;
            width: max-content;
            align-items: flex-start;
        }

        .hold-container {
            border: 1px solid #0d6efd;
            border-radius: 4px;
            padding: 6px;
            background: #fff;
            min-width: 300px;
            width: fit-content;
            align-items: center;
        }

        .hold-container.bulk {
            min-width: 180px;
            width: fit-content;
        }

        .hold-header {
            padding: 3px;
            margin: -6px -6px 6px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.8rem;
        }

        .weight-badge {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .cargo-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
            height: 140px;
            align-items: center;
            justify-content: center;
        }

        .cargo-row.bulk {
            height: 140px;
        }

        .position-column {
            display: flex;
            gap: 4px;
            width: 100%;
            justify-content: center;
            height: 65px;
            padding: 0 4px;
        }

        .position-column.left {
            order: 2;
            justify-content: flex-start;
            align-self: flex-end;
        }

        .position-column.right {
            order: 1;
            justify-content: flex-start;
            align-self: flex-start;
        }

        .position-column.center {
            order: 1;
            flex-wrap: nowrap;
            height: 140px;
            justify-content: center;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
            padding: 0 10px;
        }

        .position-column.center::-webkit-scrollbar {
            height: 6px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .position-column.center::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 4px;
        }

        .position-column.center::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        .cargo-row.bulk {
            height: 140px;
            justify-content: center;
        }

        .cargo-row.bulk .position-column {
            height: 85px;
        }

        .cargo-row.bulk .cargo-slot {
            width: 80px;
            height: 60px;
        }

        .cargo-slot {
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 3px;
            background: white;
            height: 60px;
            width: 85px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .cargo-slot.pmc {
            width: 174px;
        }

        .cargo-slot.occupied {
            background-color: #fff3cd;
        }

        .cargo-slot.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .cargo-slot.drop-target {
            border: 2px dashed #198754;
            background-color: #d1e7dd;
        }

        .container-info {
            width: 100%;
            text-align: center;
            line-height: 1;
            padding: 0 2px;
            padding-top: 12px;
            position: relative;
        }

        .position-number {
            position: absolute;
            top: 0px;
            right: 0px;
            font-size: 0.6rem;
            padding: 1px;
            color: #6c757d;
            font-weight: bold;
            line-height: 1;
            z-index: 1;
        }

        .container-id {
            font-size: 0.65rem;
        }

        .container-type,
        .container-weight {
            font-size: 0.55rem;
        }

        .position-code {
            font-size: 0.5rem;
        }

        .unplanned-area {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 6px;
            padding: 6px;
            min-height: 80px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .unplanned-area .container {
            border: 1px solid #0d6efd;
            border-radius: 3px;
            padding: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .unplanned-area .container .container-info {
            line-height: 1.2;
        }

        .unplanned-area .container .container-id {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .unplanned-area .container .container-type,
        .unplanned-area .container .container-weight {
            font-size: 0.65rem;
        }

        .unplanned-area .container:hover {
            transform: translateY(-2px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .unplanned-area .container.selected {
            background-color: #e7f1ff;
            border-color: #0d6efd;
        }

        .pmc-container {
            border-color: #fd7e14 !important;
        }

        .ake-container {
            border-color: #0dcaf0 !important;
        }

        .baggage-container {
            border-color: #198754 !important;
        }

        .cargo-container {
            border-color: #6c757d !important;
        }

        .modal.show {
            display: block;
            background-color: rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 768px) {
            .hold-container {
                min-width: 180px;
            }

            .cargo-slot {
                min-height: 60px;
            }

            .container-id {
                font-size: 0.8rem;
            }

            .container-type,
            .container-weight {
                font-size: 0.7rem;
            }
        }
    </style>
</div>
