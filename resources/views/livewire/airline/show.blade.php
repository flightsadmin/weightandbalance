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
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab === 'uld_types' ? 'active' : '' }}"
                        wire:click="$set('activeTab', 'uld_types')">
                        <i class="bi bi-box-seam"></i> ULD Types
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
                                                            @elseif ($config['type'] === 'json')
                                                                @php
                                                                    $value = $settings[$settingCategory][$key] ?? $config['default'];
                                                                @endphp
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <div class="p-2 rounded"
                                                                        style="background-color: {{ $value['color'] }}">
                                                                        <i class="bi bi-{{ $value['icon'] }} text-white"></i>
                                                                    </div>
                                                                    <div>
                                                                        <strong>{{ $value['name'] }}</strong><br>
                                                                        <small class="text-muted">
                                                                            Max: {{ $value['max_gross_weight'] }}kg
                                                                            (Tare: {{ $value['tare_weight'] }}kg)
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                {{ $settings[$settingCategory][$key] }}
                                                            @endif
                                                        @else
                                                            <span class="text-muted">Not set</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $config['type'] }}</td>
                                                    <td>{{ $config['description'] }}</td>
                                                    <td class="text-end">
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="editSetting('{{ $settingCategory }}', '{{ $key }}', {{ json_encode($config) }})">
                                                            <i class="bi bi-pencil"></i>
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
                <div class="modal fade" id="settingModal" tabindex="-1" wire:model="showSettingModal" wire:ignore.self>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form wire:submit="saveSetting">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Setting</h5>
                                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Value</label>
                                        @if ($form['type'] === 'boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" wire:model="form.value">
                                            </div>
                                        @elseif ($form['type'] === 'json')
                                            <textarea class="form-control form-control-sm" rows="10" wire:model="form.value"></textarea>
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
                                    <button type="button" class="btn btn-secondary" wire:click="closeModal">
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
            @elseif ($activeTab === 'uld_types')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">ULD Types</h5>
                        <button class="btn btn-primary btn-sm" wire:click="createUldType" data-bs-toggle="modal"
                            data-bs-target="#uldTypeModal">
                            <i class="bi bi-plus-lg"></i> Add ULD Type
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Weights</th>
                                        <th>Positions</th>
                                        <th>Allowed Holds</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($uldTypes as $key => $value)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="p-2 rounded" style="background-color: {{ $value['color'] }}">
                                                        <i class="bi bi-{{ $value['icon'] }} text-white"></i>
                                                    </div>
                                                    <strong>{{ $value['code'] }}</strong>
                                                </div>
                                            </td>
                                            <td>{{ $value['name'] }}</td>
                                            <td>
                                                <div class="small">
                                                    <div>Max: {{ $value['max_gross_weight'] }}kg</div>
                                                    <div class="text-muted">Tare: {{ $value['tare_weight'] }}kg</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div>Required: {{ $value['positions_required'] }}</div>
                                                    <div class="text-muted">
                                                        @if ($value['restrictions']['requires_adjacent_positions'])
                                                            <span class="badge bg-info">Adjacent</span>
                                                        @endif
                                                        @if ($value['restrictions']['requires_vertical_positions'])
                                                            <span class="badge bg-info">Vertical</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach ($value['allowed_holds'] as $hold)
                                                    <span class="badge bg-secondary">{{ $hold }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-1">
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click="editUldType('{{ $key }}')"
                                                        data-bs-toggle="modal" data-bs-target="#uldTypeModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="deleteUldType('{{ $key }}')"
                                                        wire:confirm="Are you sure you want to delete this ULD type?">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ULD Type Modal -->
                <div class="modal fade" id="uldTypeModal" tabindex="-1" aria-labelledby="uldTypeModalLabel" aria-hidden="true"
                    wire:ignore.self>
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form wire:submit.prevent="saveUldType">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="uldTypeModalLabel">{{ $editingUldKey ? 'Edit' : 'Create' }} ULD Type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Code</label>
                                            <input type="text" class="form-control"
                                                wire:model="uldForm.code"
                                                {{ $editingUldKey ? 'readonly' : '' }}
                                                maxlength="3">
                                            @error('uldForm.code')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" wire:model="uldForm.name">
                                            @error('uldForm.name')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tare Weight (kg)</label>
                                            <input type="number" class="form-control" wire:model="uldForm.tare_weight" step="0.1">
                                            @error('uldForm.tare_weight')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Max Gross Weight (kg)</label>
                                            <input type="number" class="form-control" wire:model="uldForm.max_gross_weight"
                                                step="0.1">
                                            @error('uldForm.max_gross_weight')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Required Positions</label>
                                            <input type="number" class="form-control" wire:model="uldForm.positions_required"
                                                min="1"
                                                max="2">
                                            @error('uldForm.positions_required')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Color</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                wire:model="uldForm.color">
                                            @error('uldForm.color')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Icon</label>
                                            <select class="form-select" wire:model="uldForm.icon">
                                                <option value="box-seam">Box</option>
                                                <option value="luggage">Luggage</option>
                                                <option value="box-seam-fill">Box (Filled)</option>
                                                <option value="container">Container</option>
                                            </select>
                                            @error('uldForm.icon')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Allowed Holds</label>
                                            <div class="d-flex gap-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="uldForm.allowed_holds"
                                                        value="FWD">
                                                    <label class="form-check-label">FWD</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="uldForm.allowed_holds"
                                                        value="AFT">
                                                    <label class="form-check-label">AFT</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="uldForm.allowed_holds"
                                                        value="BULK">
                                                    <label class="form-check-label">BULK</label>
                                                </div>
                                            </div>
                                            @error('uldForm.allowed_holds')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Restrictions</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model="uldForm.restrictions.requires_adjacent_positions">
                                                    <label class="form-check-label">Requires Adjacent Positions</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        wire:model="uldForm.restrictions.requires_vertical_positions">
                                                    <label class="form-check-label">Requires Vertical Positions</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        {{ $editingUldKey ? 'Save Changes' : 'Create ULD Type' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('uld-saved', () => {
            bootstrap.Modal.getInstance(document.getElementById('uldTypeModal')).hide();
        });

        $wire.on('setting-saved', () => {
            bootstrap.Modal.getInstance(document.getElementById('settingModal')).hide();
        });
    </script>
@endscript
