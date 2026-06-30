<?php

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
        Schema::table('families', function (Blueprint $table) {
            $table->foreignId('lingkungan_id')->nullable()->after('status_rumah')
                ->constrained('lingkungans')->restrictOnDelete();
        });

        // Migrasi data lama (kolom teks wilayah/lingkungan) ke tabel relasional baru.
        $rows = DB::table('families')
            ->select('id', 'wilayah', 'lingkungan')
            ->whereNotNull('lingkungan')
            ->where('lingkungan', '!=', '')
            ->get();

        $lingkunganIdCache = [];

        foreach ($rows as $row) {
            $namaLingkungan = trim($row->lingkungan);
            $namaWilayah = trim((string) $row->wilayah) ?: 'Belum Ditentukan';

            if (! isset($lingkunganIdCache[$namaLingkungan])) {
                $wilayahId = DB::table('wilayahs')->where('nama', $namaWilayah)->value('id');

                if (! $wilayahId) {
                    $wilayahId = DB::table('wilayahs')->insertGetId([
                        'nama'       => $namaWilayah,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $lingkunganId = DB::table('lingkungans')->where('nama', $namaLingkungan)->value('id');

                if (! $lingkunganId) {
                    $lingkunganId = DB::table('lingkungans')->insertGetId([
                        'wilayah_id' => $wilayahId,
                        'nama'       => $namaLingkungan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $lingkunganIdCache[$namaLingkungan] = $lingkunganId;
            }

            DB::table('families')->where('id', $row->id)->update([
                'lingkungan_id' => $lingkunganIdCache[$namaLingkungan],
            ]);
        }

        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn(['wilayah', 'lingkungan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('lingkungan', 100)->nullable();
            $table->string('wilayah', 100)->nullable();
        });

        $families = DB::table('families')->whereNotNull('lingkungan_id')->get();

        foreach ($families as $family) {
            $lingkungan = DB::table('lingkungans')->find($family->lingkungan_id);

            if ($lingkungan) {
                $wilayah = DB::table('wilayahs')->find($lingkungan->wilayah_id);

                DB::table('families')->where('id', $family->id)->update([
                    'lingkungan' => $lingkungan->nama,
                    'wilayah'    => $wilayah?->nama,
                ]);
            }
        }

        Schema::table('families', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lingkungan_id');
        });
    }
};
