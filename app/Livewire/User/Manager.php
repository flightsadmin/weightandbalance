<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Manager extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $roles = [];
    public $selectedRoles = [];

    public $form = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'active' => true
    ];

    public $editingUser = null;

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.email' => 'required|email|unique:users,email',
        'form.password' => 'required|min:8|confirmed',
        'form.active' => 'boolean',
        'selectedRoles' => 'array'
    ];

    public function mount()
    {
        $this->roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name
            ];
        });
    }

    public function edit(User $user)
    {
        $this->editingUser = $user;
        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'active' => $user->active
        ];
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;

        if ($this->editingUser) {
            $rules['form.email'] = 'required|email|unique:users,email,' . $this->editingUser->id;
            if (!$this->form['password']) {
                unset($rules['form.password']);
            }
        }

        $this->validate($rules);

        $userData = [
            'name' => $this->form['name'],
            'email' => $this->form['email'],
            'active' => $this->form['active']
        ];

        if ($this->form['password']) {
            $userData['password'] = Hash::make($this->form['password']);
        }

        if ($this->editingUser) {
            $this->editingUser->update($userData);
            $this->editingUser->syncRoles($this->selectedRoles);
            $message = 'User updated successfully.';
        } else {
            $user = User::create($userData);
            $user->syncRoles($this->selectedRoles);
            $message = 'User created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('user-saved');
        $this->reset(['form', 'editingUser', 'showModal', 'selectedRoles']);
    }

    public function toggleStatus(User $user)
    {
        $user->update(['active' => !$user->active]);
        $this->dispatch('alert', icon: 'success', message: 'User status updated successfully.');
    }

    public function render()
    {
        return view('livewire.user.manager', [
            'users' => User::query()
                ->with('roles')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10)
        ])->layout('components.layouts.app');
    }
}