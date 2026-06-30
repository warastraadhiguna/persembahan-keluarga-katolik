<?php

namespace App\Imports;

use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\Wilayah;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FamiliesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $skippedDuplicate = 0;
    public int $skippedInvalid = 0;

    /** No KK yang sudah diproses dalam batch impor ini (cegah duplikat dalam file yang sama) */
    protected array $seenNoKk = [];

    /** Cache id lingkungan per nama, supaya tidak query berulang dalam satu batch impor */
    protected array $lingkunganIdCache = [];

    public function model(array $row)
    {
        $row = $this->cleanRow($row);

        $namaKepalaKeluarga = $row['nama_kepala_keluarga'] ?? null;
        $noKk               = $row['no_kartu_keluarga'] ?? $row['no_kk'] ?? null;

        if (empty($namaKepalaKeluarga) || empty($noKk)) {
            $this->skippedInvalid++;
            return null;
        }

        if (in_array($noKk, $this->seenNoKk, true) || Family::where('no_kk', $noKk)->exists()) {
            $this->skippedDuplicate++;
            return null;
        }

        $this->seenNoKk[] = $noKk;
        $this->imported++;

        return new Family([
            'nama_kepala_keluarga' => $namaKepalaKeluarga,
            'no_kk'                => $noKk,
            'status_ekonomi'       => $this->normalizeStatusEkonomi($row['status_ekonomi'] ?? null),
            'jml_anggota'          => (int) ($row['jml_anggota_keluarga'] ?? $row['jml_anggota'] ?? 1),
            'status_rumah'         => $row['status_rumah'] ?? null,
            'lingkungan_id'        => $this->resolveLingkunganId($row['lingkungan'] ?? null, $row['wilayah'] ?? null),
            'is_active'            => true,
        ]);
    }

    /**
     * Cari lingkungan berdasarkan nama (cocok dengan aturan "1 lingkungan hanya milik 1 wilayah"),
     * atau buat baris baru jika belum ada. Wilayah juga dibuat otomatis bila belum ada.
     */
    protected function resolveLingkunganId(?string $namaLingkungan, ?string $namaWilayah): ?int
    {
        $namaLingkungan = trim((string) $namaLingkungan);

        if ($namaLingkungan === '') {
            return null;
        }

        if (isset($this->lingkunganIdCache[$namaLingkungan])) {
            return $this->lingkunganIdCache[$namaLingkungan];
        }

        $lingkungan = Lingkungan::where('nama', $namaLingkungan)->first();

        if (! $lingkungan) {
            $namaWilayah = trim((string) $namaWilayah) ?: 'Belum Ditentukan';
            $wilayah = Wilayah::firstOrCreate(['nama' => $namaWilayah]);

            $lingkungan = Lingkungan::create([
                'wilayah_id' => $wilayah->id,
                'nama'       => $namaLingkungan,
            ]);
        }

        return $this->lingkunganIdCache[$namaLingkungan] = $lingkungan->id;
    }

    /**
     * Hapus karakter zero-width (termasuk ‌) dan whitespace di setiap nilai sel.
     */
    protected function cleanRow(array $row): array
    {
        return array_map(function ($value) {
            if (! is_string($value)) {
                return $value;
            }

            $value = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{FEFF}]/u', '', $value);

            return trim($value);
        }, $row);
    }

    protected function normalizeStatusEkonomi(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return str_contains($value, 'pra') ? 'Pra Sejahtera' : 'Sejahtera';
    }
}
