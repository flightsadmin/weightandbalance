<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">{{ $airline->name }}</h2>
            <div>
                <button wire:click="toggleStatus" class="btn btn-sm btn-{{ $airline->active ? 'success' : 'danger' }}">
                    <i class="bi bi-{{ $airline->active ? 'check' : 'x' }}"></i>
                    {{ $airline->active ? 'Active' : 'Inactive' }}
                </button>
                <a wire:navigate href="{{ route('airlines.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs mb-2">
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'overview')">
                        <i class="bi bi-info-circle"></i> Overview
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'settings' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'settings')">
                        <i class="bi bi-gear"></i> Settings
                    </button>
                </li>
            </ul>

            @if ($activeTab === 'overview')
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title m-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th class="w-25">Name</th>
                                        <td>{{ $airline->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>IATA Code</th>
                                        <td>{{ $airline->iata_code }}</td>
                                    </tr>
                                    <tr>
                                        <th>Country</th>
                                        <td>{{ $airline->country }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $airline->phone ?: 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $airline->email ?: 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td>{{ $airline->address ?: 'Not set' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card mb-2">
                            <div class="card-header">
                                <h5 class="card-title m-0">Stats</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col">
                                        <h3 class="mb-0">{{ $airline->aircraft->count() }}</h3>
                                        <small class="text-muted">All Aircraft</small>
                                    </div>
                                    <div class="col">
                                        <h3 class="mb-0">{{ $airline->aircraft->where('active', true)->count() }}</h3>
                                        <small class="text-muted">Active Aircraft</small>
                                    </div>
                                    <div class="col">
                                        <h3 class="mb-0">{{ $airline->flights->count() }}</h3>
                                        <small class="text-muted">Recent Flights</small>
                                    </div>
                                    <div class="col">
                                        <h3 class="mb-0">{{ $airline->flights->where('status', 'scheduled')->count() }}</h3>
                                        <small class="text-muted">Scheduled</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-2">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title m-0">Active Aircraft</h5>
                            </div>
                            <div class="card-body px-2">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Registration</th>
                                                <th>Type</th>
                                                <th>Capacity</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($airline->aircraft->where('active', true)->take(5) as $aircraft)
                                                <tr>
                                                    <td>{{ $aircraft->registration_number }}</td>
                                                    <td>{{ $aircraft->type->code }}</td>
                                                    <td>{{ $aircraft->type->max_passengers }} pax</td>
                                                    <td>
                                                        <span class="badge bg-{{ $aircraft->active ? 'success' : 'warning' }}">
                                                            {{ ucfirst($aircraft->active ? 'Active' : 'Inactive') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No aircraft found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($activeTab === 'settings')
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="list-group">
                            @foreach ($defaultSettings as $category => $items)
                                <button class="list-group-item list-group-item-action {{ $settingCategory === $category ? 'active' : '' }}"
                                    wire:click="setSettingCategory('{{ $category }}')">
                                    <i
                                        class="bi bi-{{ match ($category) {
                                            'general' => 'gear',
                                            'operations' => 'clock',
                                            'cargo' => 'box',
                                            'notifications' => 'bell',
                                            default => 'circle',
                                        } }}"></i>
                                    {{ str_replace('_', ' ', ucfirst($category)) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title m-0">{{ str_replace('_', ' ', ucfirst($settingCategory)) }} Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Setting</th>
                                                <th>Value</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($currentSettings as $key => $config)
                                                <tr>
                                                    <td>{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                                    <td>
                                                        @if (isset($settings[$settingCategory][$key]))
                                                            @if ($config['type'] === 'boolean')
                                                                <span
                                                                    class="badge bg-{{ $settings[$settingCategory][$key] ? 'success' : 'danger' }}">
                                                                    {{ $settings[$settingCategory][$key] ? 'Yes' : 'No' }}
                                                                </span>
                                                            @else
                                                                {{ $settings[$settingCategory][$key] }}
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $config['type'] }}</td>
                                                    <td>{{ $config['description'] }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="editSetting('{{ $settingCategory }}', '{{ $key }}', {{ json_encode($config) }})"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#settingModal">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setting Modal -->
                <div class="modal fade" id="settingModal" tabindex="-1" wire:ignore.self>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form wire:submit="saveSetting">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Setting</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Value</label>
                                        @if ($form['type'] === 'boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:model="form.value">
                                            </div>
                                        @else
                                            <input
                                                type="{{ $form['type'] === 'float' || $form['type'] === 'integer' ? 'number' : 'text' }}"
                                                class="form-control form-control-sm"
                                                step="{{ $form['type'] === 'float' ? '0.01' : '1' }}"
                                                wire:model="form.value">
                                        @endif
                                        @error('form.value')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="text-muted small">
                                        {{ $form['description'] }}
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @script
                    <script>
                        $wire.on('setting-saved', () => {
                            bootstrap.Modal.getInstance(document.getElementById('settingModal')).hide();
                        });
                    </script>
                @endscript
            @endif
        </div>
    </div>
</div>
