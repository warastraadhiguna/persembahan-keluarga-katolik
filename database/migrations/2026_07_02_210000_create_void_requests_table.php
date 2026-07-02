<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('void_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 500)->nullable();
            $table->timestamps();
        });

        // Default: Bendahara punya otoritas persetujuan void
        $now = now();
        DB::table('role_permissions')->insertOrIgnore([
            ['role' => 'bendahara', 'menu' => 'persetujuan-void', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('menu', 'persetujuan-void')->delete();
        Schema::dropIfExists('void_requests');
    }
};
