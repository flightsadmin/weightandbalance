<div>
    <div>
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <button class="nav-link {{ $tab === 'users' ? 'active' : '' }}"
                            wire:click="setTab('users')">
                            <i class="bi bi-people"></i> Users
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $tab === 'roles' ? 'active' : '' }}"
                            wire:click="setTab('roles')">
                            <i class="bi bi-shield-shaded"></i> Roles
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $tab === 'permissions' ? 'active' : '' }}"
                            wire:click="setTab('permissions')">
                            <i class="bi bi-house-lock-fill"></i> Permissions
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content">
                    <div class="tab-pane fade {{ $tab === 'users' ? 'show active' : '' }}">
                        <livewire:admin.user.manager lazy />
                    </div>
                    <div class="tab-pane fade {{ $tab === 'roles' ? 'show active' : '' }}">
                        <livewire:admin.role.manager lazy />
                    </div>
                    <div class="tab-pane fade {{ $tab === 'permissions' ? 'show active' : '' }}">
                        <livewire:admin.permission.manager lazy />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-tabs .nav-link {
            border: none;
            color: var(--bs-body-color);
            padding: 0.75rem 1rem;
            margin-right: 0.25rem;
            transition: all 0.2s;
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--bs-primary);
        }

        .nav-tabs .nav-link.active {
            border: none;
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
        }

        .tab-pane {
            padding: 1rem;
        }
    </style>
</div>
