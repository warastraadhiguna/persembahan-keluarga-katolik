<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('print_settings', 'qr_size')) {
            Schema::table('print_settings', function (Blueprint $table) {
                $table->unsignedTinyInteger('qr_size')->default(55)->after('gap');
            });
        }
    }

    public function down(): void
    {
        Schema::table('print_settings', function (Blueprint $table) {
            $table->dropColumn('qr_size');
        });
    }
};
