<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title m-0">{{ $airline->name }} Settings</h2>
                <p class="text-muted small m-0">Manage airline-specific settings and configurations</p>
            </div>
            <div class="d-flex align-items-center">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search settings...">
            </div>
            <a wire:navigate href="{{ route('airlines.show', $airline) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Airline
            </a>
        </div>

        <div class="card-body">
            @if ($settings->isEmpty() && !$search)
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    No settings have been configured for this airline yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Setting</th>
                                <th>Description</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($settings as $setting)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $setting->key)) }}</td>
                                    <td>{{ $setting->description }}</td>
                                    <td>{{ $setting->value }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal"
                                            data-bs-target="#editSetting{{ $setting->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editSetting{{ $setting->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Value</label>
                                                            <input type="text" class="form-control"
                                                                wire:model.live="settings.{{ $setting->id }}.value"
                                                                value="{{ $setting->value }}">
                                                            <div class="form-text">{{ $setting->description }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            wire:click="updateValue({{ $setting->id }}, '{{ $setting->value }}')"
                                                            data-bs-dismiss="modal">
                                                            Save Changes
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No settings found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-4">
                {{ $settings->links() }}
            </div>
        </div>
    </div>
</div>
