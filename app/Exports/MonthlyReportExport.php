<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Services\PersembahanReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private int $bulan,
        private int $tahun,
        private ?int $wilayahId,
        private ?int $lingkunganId,
        private ?int $userId,
    ) {}

    public function collection(): Collection
    {
        return app(PersembahanReportService::class)
            ->monthly($this->bulan, $this->tahun, $this->wilayahId, $this->lingkunganId, $this->userId)
            ->values()
            ->map(fn ($row, $i) => [
                $i + 1,
                $row['family']->kode_keluarga,
                $row['family']->nama_kepala_keluarga,
                $row['family']->lingkungan?->nama,
                $row['family']->lingkungan?->wilayah?->nama,
                $row['sudah_bayar'] ? 'Sudah Bayar' : 'Belum Bayar',
                $row['nominal'],
                $row['petugas'],
            ]);
    }

    public function headings(): array
    {
        return [
            '#', 'Kode Keluarga', 'Nama Kepala Keluarga', 'Lingkungan', 'Wilayah',
            'Status', 'Nominal', 'Petugas',
        ];
    }

    public function title(): string
    {
        return 'Rekap '.Transaction::monthLabel($this->bulan).' '.$this->tahun;
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
