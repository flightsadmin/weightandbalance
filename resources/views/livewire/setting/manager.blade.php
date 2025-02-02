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
            @endif
        </div>
    </div>
</div>
