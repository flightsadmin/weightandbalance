<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3>{{ $aircraftType->name }}</h3>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3">
                <div class="list-group">
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'overview' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'overview')">
                        <i class="bi bi-info-circle"></i> Overview
                    </button>
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'holds' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'holds')">
                        <i class="bi bi-box"></i> Holds
                    </button>
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'zones' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'zones')">
                        <i class="bi bi-diagram-3-fill"></i> Cabin Zones
                    </button>
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'aircraft' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'aircraft')">
                        <i class="bi bi-airplane"></i> Aircraft
                    </button>
                    <button class="list-group-item list-group-item-action {{ $activeTab === 'settings' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'settings')">
                        <i class="bi bi-gear"></i> Settings
                    </button>
                </div>
            </div>

            <div class="col-md-9">
                @if ($activeTab === 'overview')
                    <livewire:aircraft-type.overview :aircraftType="$aircraftType" />
                @elseif ($activeTab === 'holds')
                    <livewire:aircraft-type.hold-manager :aircraftType="$aircraftType" />
                @elseif ($activeTab === 'zones')
                    <livewire:aircraft-type.zone-manager :aircraftType="$aircraftType" />
                @elseif ($activeTab === 'aircraft')
                    <livewire:aircraft-type.aircraft-manager :aircraftType="$aircraftType" />
                @elseif ($activeTab === 'settings')
                    <livewire:aircraft-type.settings :aircraftType="$aircraftType" />
                @endif
            </div>
        </div>
    </div>
</div>
