<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lingkungans', function (Blueprint $table) {
            // Hapus unique hanya pada nama (nama bisa sama di wilayah berbeda)
            $table->dropUnique('lingkungans_nama_unique');

            // Unique pada kombinasi wilayah_id + nama
            $table->unique(['wilayah_id', 'nama'], 'lingkungans_wilayah_nama_unique');
        });
    }

    public function down(): void
    {
        Schema::table('lingkungans', function (Blueprint $table) {
            $table->dropUnique('lingkungans_wilayah_nama_unique');
            $table->unique('nama', 'lingkungans_nama_unique');
        });
    }
};
