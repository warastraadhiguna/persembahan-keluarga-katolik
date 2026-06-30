<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 20);
            $table->string('menu', 50);
            $table->timestamps();
            $table->unique(['role', 'menu']);
        });

        // Seed sesuai pengaturan default yang sebelumnya hardcoded.
        // Admin tidak perlu baris di sini karena selalu punya akses penuh.
        $defaults = [
            Role::Operator->value  => ['keluarga', 'persembahan', 'wilayah-lingkungan'],
            Role::Bendahara->value => ['persembahan', 'laporan', 'audit'],
        ];

        $now  = now();
        $rows = [];

        foreach ($defaults as $role => $menus) {
            foreach ($menus as $menu) {
                $rows[] = [
                    'role'       => $role,
                    'menu'       => $menu,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permissions')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
