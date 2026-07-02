<?php

namespace App\Enums;

enum Role: string
{
    case Admin     = 'admin';
    case Operator  = 'operator';
    case Bendahara = 'bendahara';
    case Pimpinan  = 'pimpinan';

    public function label(): string
    {
        return match($this) {
            self::Admin     => 'Admin',
            self::Operator  => 'Operator',
            self::Bendahara => 'Bendahara',
            self::Pimpinan  => 'Pimpinan Gereja',
        };
    }

    /**
     * Daftar menu yang bisa diatur lewat halaman "Hak Akses Role" (di luar dashboard,
     * yang selalu bisa diakses semua role, dan role-permission, yang khusus admin).
     */
    public static function configurableMenus(): array
    {
        return [
            'pengguna'           => 'Pengguna',
            'keluarga'           => 'Umat / Keluarga',
            'persembahan'        => 'Persembahan',
            'laporan'            => 'Laporan',
            'audit'              => 'Log Audit',
            'wilayah-lingkungan' => 'Wilayah & Lingkungan',
            'gereja'             => 'Data Gereja',
            'nominal-presets'    => 'Preset Nominal',
            'persetujuan-void'   => 'Persetujuan Pembatalan',
        ];
    }

    /** Role yang bisa diatur hak aksesnya lewat halaman pengaturan (admin selalu akses penuh). */
    public static function configurableRoles(): array
    {
        return [self::Operator, self::Bendahara, self::Pimpinan];
    }

    public function canAccess(string $menu): bool
    {
        if ($this === self::Admin) {
            return true;
        }

        if ($menu === 'role-permission') {
            return false;
        }

        return in_array($menu, \App\Models\RolePermission::menusForRole($this), true);
    }

    public static function options(): array
    {
        return array_map(fn($r) => ['value' => $r->value, 'label' => $r->label()], self::cases());
    }
}
