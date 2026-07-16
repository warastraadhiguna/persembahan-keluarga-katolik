<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wilayahs', function (Blueprint $table) {
            $table->string('kode', 5)->nullable()->after('id');
        });

        Schema::table('lingkungans', function (Blueprint $table) {
            $table->string('kode', 5)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('wilayahs', function (Blueprint $table) {
            $table->dropColumn('kode');
        });

        Schema::table('lingkungans', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};
