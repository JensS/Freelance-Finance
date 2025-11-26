<?php

namespace App\Livewire\Users;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Benutzer')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // Create/Edit modal state
    public bool $showModal = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'tax_accountant';

    // Delete confirmation
    public bool $showDeleteConfirm = false;

    public ?int $deleteUserId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation', 'role']);
        $this->role = Role::TaxAccountant->value;
        $this->showModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation', 'role']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'role' => ['required', Rule::enum(Role::class)],
        ];

        // Password required for new users
        if (! $this->editingUserId) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            // Password optional for editing, but if provided must be confirmed
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);

            // Prevent demoting self from owner role
            if ($user->id === Auth::id() && $user->role === Role::Owner && $this->role !== Role::Owner->value) {
                session()->flash('error', 'Sie können sich nicht selbst die Inhaber-Rolle entziehen.');

                return;
            }

            $user->update($data);
            session()->flash('success', 'Benutzer erfolgreich aktualisiert.');
        } else {
            User::create($data);
            session()->flash('success', 'Benutzer erfolgreich erstellt.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $userId): void
    {
        $this->deleteUserId = $userId;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteUserId = null;
        $this->showDeleteConfirm = false;
    }

    public function delete(): void
    {
        if (! $this->deleteUserId) {
            return;
        }

        $user = User::find($this->deleteUserId);

        if (! $user) {
            session()->flash('error', 'Benutzer nicht gefunden.');
            $this->cancelDelete();

            return;
        }

        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            session()->flash('error', 'Sie können sich nicht selbst löschen.');
            $this->cancelDelete();

            return;
        }

        $user->delete();
        session()->flash('success', 'Benutzer erfolgreich gelöscht.');
        $this->cancelDelete();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => Role::cases(),
        ]);
    }
}
