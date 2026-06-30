<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('kode_keluarga', 10)->unique();
            $table->string('qr_token', 32)->unique();
            $table->string('nama_kepala_keluarga', 100);
            $table->string('no_kk', 16)->unique();
            $table->enum('status_ekonomi', ['Sejahtera', 'Pra Sejahtera'])->default('Sejahtera');
            $table->unsignedTinyInteger('jml_anggota')->default(1);
            $table->string('status_rumah', 50)->nullable();
            $table->string('lingkungan', 100)->nullable();
            $table->string('wilayah', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
