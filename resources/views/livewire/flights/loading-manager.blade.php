<div>
    <div class="card" x-data="{
        holds: @js($holds),
        containers: @js($containers),
        initialState: JSON.stringify(@js($containers)),
        selectedContainer: null,
        localStorageKey: 'loadplan-' + @js($flight->id),
        showWeightSummary: false,
        showAssignModal: false,
        searchQuery: '',
        searchResults: [],
        newContainer: {
            uld_code: '',
            type: 'AKE',
            weight: 0,
            pieces: 0,
            content_type: 'cargo',
        },
    
        init() {
            this.loadFromStorage();
            this.$wire.on('resetAlpineState', () => this.resetState());
            this.calculateWeights();
        },
    
        hasChanges() {
            const currentState = JSON.stringify(this.containers);
            return currentState !== this.initialState;
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
    
                const hold = this.holds.find(h => h.positions.some(p => p.id === position.id));
    
                // Update container position
                this.selectedContainer.position = position.id;
                this.selectedContainer.position_code = position.designation;
                this.selectedContainer.hold_name = hold.name;
                this.selectedContainer.updated_at = new Date().toISOString();
    
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
            container.position_code = null;
            container.hold_name = null;
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
            return this.containers.some(c => c.position === position.id);
        },
    
        getContainerInPosition(position) {
            return this.containers.find(c => c.position === position.id);
        },
    
        getHoldWeight(hold) {
            return this.containers
                .filter(c => hold.positions.some(pos => pos.id === c.position))
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
            this.initialState = JSON.stringify(this.containers);
            this.calculateWeights();
        },
    
        async saveToServer() {
            await this.$wire.saveLoadplan(this.containers);
            localStorage.removeItem(this.localStorageKey);
            this.initialState = JSON.stringify(this.containers);
            this.loadFromStorage();
            this.calculateWeights();
        },
    
        calculateWeights() {
            this.holds.forEach(hold => {
                hold.currentWeight = this.getHoldWeight(hold);
                hold.utilization = this.getHoldUtilization(hold);
            });
        },
    
        async searchContainers() {
            if (!this.searchQuery.trim()) {
                this.searchResults = [];
                return;
            }
            this.searchResults = await this.$wire.searchContainers(this.searchQuery);
        },
    
        async attachContainer(container) {
            const response = await this.$wire.attachContainer(container.id);
    
            if (response.success) {
                // Add the new container to the containers list
                this.containers.push(response.container);
    
                // Mark the container as attached in search results
                const resultContainer = this.searchResults.find(c => c.id === container.id);
                if (resultContainer) {
                    resultContainer.attached = true;
                }
    
                // Show success message
                this.$dispatch('notify', {
                    type: 'success',
                    message: response.message
                });
            } else {
                // Show error message
                this.$dispatch('notify', {
                    type: 'error',
                    message: response.message
                });
            }
        },
    
        addContainer() {
            if (!this.newContainer.uld_code) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'ULD code is required'
                });
                return;
            }
    
            if (this.newContainer.weight <= 0) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Weight must be greater than 0'
                });
                return;
            }
    
            // Add new container to the list
            const newContainerId = Date.now(); // Temporary ID for new container
            this.containers.push({
                id: newContainerId,
                uld_code: this.newContainer.uld_code,
                type: this.newContainer.type,
                weight: parseFloat(this.newContainer.weight),
                pieces: parseInt(this.newContainer.pieces),
                position: null,
                position_code: null,
                status: 'unloaded',
                destination: @js($flight->arrival_airport),
                content_type: this.newContainer.content_type,
                updated_at: new Date().toISOString(),
            });
    
            // Reset form and close modal
            this.newContainer = {
                uld_code: '',
                type: 'AKE',
                weight: 0,
                pieces: 0,
                content_type: 'cargo',
            };
            this.showAssignModal = false;
            this.saveState();
            this.calculateWeights();
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
                <button class="btn btn-sm btn-outline-success" @click="showAssignModal = true">
                    <i class="bi bi-plus-circle"></i> Attach Container
                </button>
                <button class="btn btn-sm btn-outline-primary" @click="showWeightSummary = !showWeightSummary">
                    <i class="bi bi-clipboard-data"></i> Weight Summary
                </button>
                <button class="btn btn-sm btn-outline-success"
                    @click="saveToServer"
                    :disabled="!hasChanges()"
                    :class="{ 'opacity-50': !hasChanges() }">
                    <i class="bi bi-check-circle"></i> Finalize Load Plan
                </button>
                <button class="btn btn-sm btn-outline-danger"
                    @click="resetState(); $wire.resetLoadplan()">
                    <i class="bi bi-arrow-counterclockwise"></i> Offload All
                </button>
            </div>
        </div>

        <!-- Container Assignment Modal -->
        <div class="modal fade" :class="{ 'show': showAssignModal }" x-show="showAssignModal"
            tabindex="-1" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attach Container</h5>
                        <button type="button" class="btn-close" @click="showAssignModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Search Container</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm"
                                    x-model="searchQuery" @input.debounce="searchContainers" placeholder="Enter ULD number">
                                <button class="btn btn-outline-secondary" type="button" @click="searchContainers">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="search-results mt-3" x-show="searchResults.length > 0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>ULD Number</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="container in searchResults" :key="container.id">
                                            <tr>
                                                <td x-text="container.container_number"></td>
                                                <td>
                                                    <span x-show="containers.some(c => c.id === container.id)"
                                                        class="badge bg-success">Attached</span>
                                                    <span x-show="!containers.some(c => c.id === container.id)"
                                                        class="badge bg-secondary">Available</span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm"
                                                        :class="containers.some(c => c.id === container.id) ? 'btn-success' : 'btn-primary'"
                                                        @click="attachContainer(container)"
                                                        :disabled="containers.some(c => c.id === container.id)">
                                                        <i class="bi"
                                                            :class="containers.some(c => c.id === container.id) ? 'bi-check-circle' :
                                                                'bi-plus-circle'"></i>
                                                        <span
                                                            x-text="containers.some(c => c.id === container.id) ? 'Attached' : 'Attach'"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div x-show="searchQuery && !searchResults.length" class="text-center py-3">
                            <p class="text-muted">No containers found</p>
                        </div>
                    </div>
                </div>
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
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <livewire:container.manager :flight="$flight" /> {{--  --}}
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

        .unplanned-area .container:hover {
            transform: translateY(-2px);
            background: #a4c0dd;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .unplanned-area .container.selected {
            background-color: #93bdf8;
            border-color: #0d6efd;
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
