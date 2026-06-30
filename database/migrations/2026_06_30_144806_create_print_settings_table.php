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
        Schema::create('print_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('rows')->default(8);
            $table->unsignedTinyInteger('cols')->default(3);
            $table->unsignedInteger('start')->default(1);
            $table->string('paper', 20)->default('a4');
            $table->decimal('paper_width', 6, 1)->default(210);
            $table->decimal('paper_height', 6, 1)->default(297);
            $table->decimal('margin', 5, 1)->default(10);
            $table->decimal('gap', 5, 1)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_settings');
    }
};
