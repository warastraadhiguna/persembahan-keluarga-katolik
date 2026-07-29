<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','bendahara','pimpinan','sekretariat') NOT NULL DEFAULT 'operator'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','operator','bendahara','pimpinan') NOT NULL DEFAULT 'operator'");
    }
};
