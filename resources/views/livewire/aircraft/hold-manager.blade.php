<div>
    <button class="btn btn-primary btn-sm" wire:click="$toggle('showModal')" data-bs-toggle="modal"
        data-bs-target="#holdFormModal">
        <i class="bi bi-plus-circle"></i> Add Hold
    </button>

    <!-- Modal -->
    <div class="modal fade" id="holdFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingHold ? 'Edit' : 'Add' }} Hold</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <select name="name" class="form-select form-select-sm" wire:model="form.name">
                                    <option value="">Select Name</option>
                                    <option value="Aft Hold">Aft Hold</option>
                                    <option value="Fwd Hold">Fwd Hold</option>
                                    <option value="Bulk Hold">Bulk Hold</option>
                                </select>
                                @error('form.name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <select class="form-select form-select-sm" wire:model="form.code">
                                    <option value="">Select Code</option>
                                    <option value="AH">AH (Aft Hold)</option>
                                    <option value="FH">FH (Fwd Hold)</option>
                                    <option value="BH">BH (Bulk Hold)</option>
                                </select>
                                @error('form.code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.position">
                                @error('form.position')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.max_weight">
                                @error('form.max_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="form.is_active">
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Hold</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('hold-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('holdFormModal'));
                modal.hide();
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('edit-hold', (event) => {
                    @this.edit(event.holdId);
                    // Open the modal
                    const modal = new bootstrap.Modal(document.getElementById('holdFormModal'));
                    modal.show();
                });
            });
        </script>
    @endscript
</div>
