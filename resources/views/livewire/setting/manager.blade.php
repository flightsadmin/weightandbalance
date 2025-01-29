<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">{{ $airline->name }} Settings</h2>
            <a wire:navigate href="{{ route('airlines.show', $airline) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <button class="nav-link {{ str_contains($activeTab, 'settings') ? 'active' : '' }}"
                        wire:click="setTab('settings.general')">
                        <i class="bi bi-gear"></i> Airline Settings
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'aircraft' ? 'active' : '' }}"
                        wire:click="setTab('aircraft')">
                        <i class="bi bi-airplane"></i> Aircraft Types
                    </button>
                </li>
            </ul>

            @if (str_contains($activeTab, 'settings'))
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="list-group">
                            <button class="list-group-item list-group-item-action {{ $activeTab === 'settings.general' ? 'active' : '' }}"
                                wire:click="setTab('settings.general')">
                                <i class="bi bi-gear"></i> General Settings
                            </button>
                            <button
                                class="list-group-item list-group-item-action {{ $activeTab === 'settings.operations' ? 'active' : '' }}"
                                wire:click="setTab('settings.operations')">
                                <i class="bi bi-clock"></i> Operations
                            </button>
                            <button class="list-group-item list-group-item-action {{ $activeTab === 'settings.cargo' ? 'active' : '' }}"
                                wire:click="setTab('settings.cargo')">
                                <i class="bi bi-box"></i> Cargo Settings
                            </button>
                            <button
                                class="list-group-item list-group-item-action {{ $activeTab === 'settings.notifications' ? 'active' : '' }}"
                                wire:click="setTab('settings.notifications')">
                                <i class="bi bi-bell"></i> Notifications
                            </button>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title m-0">{{ ucfirst(explode('.', $activeTab)[1]) }} Settings</h5>
                                <button class="btn btn-primary btn-sm"
                                    wire:click="saveSettings('{{ explode('.', $activeTab)[1] }}')">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                            <div class="card-body">
                                <form wire:submit.prevent="saveSettings('{{ explode('.', $activeTab)[1] }}')">
                                    <div class="row g-3">
                                        @foreach ($settings[explode('.', $activeTab)[1]] as $key => $value)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">
                                                        {{ ucwords(str_replace('_', ' ', $key)) }}
                                                    </label>
                                                    @if (str_contains($key, 'allowed') || str_contains($key, 'enable'))
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" class="form-check-input"
                                                                wire:model.live="settings.{{ explode('.', $activeTab)[1] }}.{{ $key }}"
                                                                @checked($value)>
                                                        </div>
                                                    @else
                                                        <input type="{{ $this->getInputType($key) }}"
                                                            class="form-control form-control-sm"
                                                            wire:model.live="settings.{{ explode('.', $activeTab)[1] }}.{{ $key }}"
                                                            placeholder="{{ $this->getSettingDescription($key) }}">
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title m-0">Aircraft Types</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                @forelse ($aircraftTypes as $type)
                                    <button wire:click="selectType({{ $type->id }})"
                                        class="list-group-item list-group-item-action {{ $selectedType?->id === $type->id ? 'active' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <strong>{{ $type->code }}</strong>
                                                    <span class="badge bg-secondary">{{ $type->category }}</span>
                                                </div>
                                                <small class="text-muted">{{ $type->manufacturer }} {{ $type->name }}</small>
                                            </div>
                                        </div>
                                        <div class="small mt-1">
                                            <div class="row text-muted">
                                                <div class="col">
                                                    <i class="bi bi-person"></i> {{ $type->max_passengers }} pax
                                                </div>
                                                <div class="col">
                                                    <i class="bi bi-box"></i> {{ number_format($type->cargo_capacity) }} kg
                                                </div>
                                            </div>
                                            <div class="row text-muted">
                                                <div class="col">
                                                    <i class="bi bi-arrow-up"></i> {{ number_format($type->max_takeoff_weight) }} kg
                                                </div>
                                                <div class="col">
                                                    <i class="bi bi-fuel-pump"></i> {{ number_format($type->max_fuel_capacity) }} L
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4">
                                        <i class="bi bi-exclamation-circle display-6"></i>
                                        <p class="mt-2">No aircraft types found</p>
                                        <small> <a href="{{ route('aircraft_types.index') }}"
                                                class="btn btn-sm btn-link text-decoration-none">
                                                Add aircraft types here</a>
                                        </small>
                                    </div>
                                @endforelse
                            </div>
                            @if ($aircraftTypes->isNotEmpty())
                                <div class="card-footer text-center">
                                    <a href="{{ route('aircraft_types.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-gear"></i> Manage Aircraft Types
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-9">
                        @if ($selectedType)
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title m-0">
                                        {{ $selectedType->manufacturer }} {{ $selectedType->name }}
                                        <small class="text-muted">({{ $selectedType->code }})</small>
                                    </h5>
                                    <button type="submit" form="settings-form" class="btn btn-sm btn-primary">
                                        <i class="bi bi-save"></i> Save Settings
                                    </button>
                                </div>
                                <div class="card-body">
                                    <form id="settings-form" wire:submit="saveTypeSettings">
                                        <div class="row g-3">
                                            @foreach ($settings['aircraft'] as $key => $value)
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">
                                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                                        </label>
                                                        @if (str_contains($key, 'allowed') || str_contains($key, 'enable'))
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input"
                                                                    wire:model.live="settings.aircraft.{{ $key }}"
                                                                    @checked($value)>
                                                            </div>
                                                        @else
                                                            <input type="{{ $this->getInputType($key) }}"
                                                                class="form-control form-control-sm"
                                                                wire:model.live="settings.aircraft.{{ $key }}"
                                                                placeholder="{{ $this->getSettingDescription($key) }}">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title m-0">Aircraft Settings</h5>
                                </div>
                                <div class="card-body text-center text-muted my-5">
                                    <i class="bi bi-airplane display-4"></i>
                                    <p class="mt-3">Select an aircraft type to view its settings</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
