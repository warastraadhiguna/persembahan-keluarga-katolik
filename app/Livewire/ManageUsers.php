<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageUsers extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterRole = '';

    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'operator';
    public bool $is_active = true;

    public bool $showFormModal = false;
    public bool $showPasswordModal = false;
    public bool $showDeleteConfirm = false;
    public ?int $deletingId = null;

    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public ?int $resetPasswordUserId = null;

    protected function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role'      => ['required', Rule::enum(Role::class)],
            'is_active' => ['boolean'],
        ];
    }

    protected array $messages = [
        'name.required'  => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.unique'   => 'Email sudah digunakan.',
        'role.required'  => 'Role wajib dipilih.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterRole, fn($q) => $q->where('role', $this->filterRole))
            ->orderBy('name')
            ->paginate(10);
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'email', 'role', 'is_active']);
        $this->role      = 'operator';
        $this->is_active = true;
        $this->showFormModal = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->role->value;
        $this->is_active = $user->is_active;
        $this->showFormModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'role'      => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Pengguna berhasil diperbarui.');
        } else {
            $data['password'] = Hash::make('password');
            User::create($data);
            session()->flash('success', 'Pengguna ditambahkan. Password default: password');
        }

        $this->showFormModal = false;
        unset($this->users);
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak dapat menonaktifkan akun Anda sendiri.');
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Pengguna berhasil {$status}.");
        unset($this->users);
    }

    public function openResetPassword(int $id): void
    {
        $this->resetPasswordUserId = $id;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->showPasswordModal = true;
        $this->resetErrorBag();
    }

    public function resetPassword(): void
    {
        $this->validate([
            'newPassword' => ['required', 'min:8', 'same:newPasswordConfirmation'],
        ], [
            'newPassword.required' => 'Password baru wajib diisi.',
            'newPassword.min'      => 'Password minimal 8 karakter.',
            'newPassword.same'     => 'Konfirmasi password tidak cocok.',
        ]);

        User::findOrFail($this->resetPasswordUserId)
            ->update(['password' => Hash::make($this->newPassword)]);

        $this->showPasswordModal = false;
        session()->flash('success', 'Password berhasil direset.');
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Tidak dapat menghapus akun Anda sendiri.');
            return;
        }
        $this->deletingId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        User::findOrFail($this->deletingId)->delete();
        $this->showDeleteConfirm = false;
        session()->flash('success', 'Pengguna berhasil dihapus.');
        unset($this->users);
    }

    public function render()
    {
        return view('livewire.manage-users');
    }
}
