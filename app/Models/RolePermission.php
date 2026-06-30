<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['role', 'menu'])]
class RolePermission extends Model
{
    /** Menu yang diizinkan untuk role tertentu (operator/bendahara), hasilnya di-cache. */
    public static function menusForRole(Role $role): array
    {
        return Cache::rememberForever(
            static::cacheKey($role),
            fn () => static::query()->where('role', $role->value)->pluck('menu')->all()
        );
    }

    /** Simpan ulang daftar menu yang diizinkan untuk satu role (replace semua). */
    public static function syncMenusForRole(Role $role, array $menus): void
    {
        static::query()->where('role', $role->value)->delete();

        $now = now();

        $rows = array_map(fn (string $menu) => [
            'role'       => $role->value,
            'menu'       => $menu,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_values(array_unique($menus)));

        if (! empty($rows)) {
            static::query()->insert($rows);
        }

        static::forgetCache($role);
    }

    protected static function cacheKey(Role $role): string
    {
        return "role_permissions:{$role->value}";
    }

    protected static function forgetCache(Role $role): void
    {
        Cache::forget(static::cacheKey($role));
    }
}
