<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => Role::class,
            'is_active'         => 'boolean',
        ];
    }

    public function hasRole(Role|string $role): bool
    {
        $roleEnum = $role instanceof Role ? $role : Role::from($role);
        return $this->role === $roleEnum;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function canAccessMenu(string $menu): bool
    {
        return $this->role?->canAccess($menu) ?? false;
    }
}
