<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DatabaseExcelExport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            new DatabaseSheetExport('families', 'Keluarga', [
                'id', 'kode_keluarga', 'nama_kepala_keluarga', 'no_kk',
                'status_ekonomi', 'jml_anggota', 'status_rumah', 'no_hp',
                'lingkungan_id', 'is_active', 'qr_token', 'created_at',
            ]),
            new DatabaseSheetExport('transactions', 'Transaksi', [
                'id', 'family_id', 'user_id', 'tahun', 'bulan', 'tanggal',
                'nominal', 'catatan', 'is_void', 'voided_at', 'created_at',
            ]),
            new DatabaseSheetExport('wilayahs', 'Wilayah', [
                'id', 'nama', 'created_at',
            ]),
            new DatabaseSheetExport('lingkungans', 'Lingkungan', [
                'id', 'nama', 'wilayah_id', 'created_at',
            ]),
            new DatabaseSheetExport('users', 'Pengguna', [
                'id', 'name', 'email', 'role', 'is_active', 'created_at',
            ]),
        ];
    }
}
