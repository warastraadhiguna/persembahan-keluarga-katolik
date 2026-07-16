<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('no_kk', 16)->nullable()->change();
            $table->string('status_ekonomi', 20)->nullable()->default(null)->change();
            $table->unsignedTinyInteger('jml_anggota')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('no_kk', 16)->nullable(false)->change();
            $table->enum('status_ekonomi', ['Sejahtera', 'Pra Sejahtera'])->nullable(false)->default('Sejahtera')->change();
            $table->unsignedTinyInteger('jml_anggota')->nullable(false)->default(1)->change();
        });
    }
};
