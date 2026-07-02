<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL 5.7: alter enum column to add 'pimpinan'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','bendahara','pimpinan') NOT NULL DEFAULT 'operator'");

        // Seed default permission: pimpinan hanya bisa akses laporan
        $now = now();
        DB::table('role_permissions')->insertOrIgnore([
            ['role' => 'pimpinan', 'menu' => 'laporan', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('role', 'pimpinan')->delete();

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','bendahara') NOT NULL DEFAULT 'operator'");
    }
};
