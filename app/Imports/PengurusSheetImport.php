<?php

namespace App\Imports;

use App\Models\Family;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Import satu sheet dari file "data keluarga" format pengurus.
 * Format sheet: NIKK | NMKK | LINGK | WIL (baris 1 = header, data mulai baris 2)
 */
class PengurusSheetImport implements ToModel, WithStartRow, SkipsEmptyRows
{
    public int $imported    = 0;
    public int $skipped     = 0;

    public function __construct(private ?int $lingkunganId) {}

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row): ?Family
    {
        $kode = trim((string) ($row[0] ?? ''));
        $nama = trim((string) ($row[1] ?? ''));

        if ($kode === '' || $nama === '') {
            return null;
        }

        // Lewati sel yang isinya tanggal numerik atau format salah
        if (! preg_match('/^\d{1,2}\.\d{2}\.\d+$/', $kode)) {
            $this->skipped++;
            return null;
        }

        // Lewati duplikat
        if (Family::where('kode_keluarga', $kode)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new Family([
            'kode_keluarga'       => $kode,
            'nama_kepala_keluarga' => $nama,
            'lingkungan_id'       => $this->lingkunganId,
            'is_active'           => true,
        ]);
    }
}
