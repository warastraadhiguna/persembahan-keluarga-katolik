<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\RolePermission;
use Livewire\Component;

class RolePermissionManager extends Component
{
    /** @var array<string, array<string, bool>> menu => role => bool */
    public array $matrix = [];

    public function mount(): void
    {
        $this->loadMatrix();
    }

    protected function loadMatrix(): void
    {
        $matrix = [];

        foreach (array_keys(Role::configurableMenus()) as $menu) {
            foreach (Role::configurableRoles() as $role) {
                $matrix[$menu][$role->value] = in_array($menu, RolePermission::menusForRole($role), true);
            }
        }

        $this->matrix = $matrix;
    }

    public function save(): void
    {
        foreach (Role::configurableRoles() as $role) {
            $menus = [];

            foreach (array_keys(Role::configurableMenus()) as $menu) {
                if (! empty($this->matrix[$menu][$role->value])) {
                    $menus[] = $menu;
                }
            }

            RolePermission::syncMenusForRole($role, $menus);
        }

        $this->loadMatrix();
        session()->flash('success', 'Hak akses role berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.role-permission-manager');
    }
}
