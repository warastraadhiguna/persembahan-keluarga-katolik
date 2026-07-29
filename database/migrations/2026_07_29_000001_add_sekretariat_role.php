<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('role_permissions')->insertOrIgnore([
            ['role' => 'sekretariat', 'menu' => 'keluarga', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('role', 'sekretariat')->delete();
    }
};
