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

class YearlyReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private int $tahun,
        private ?int $wilayahId,
        private ?int $lingkunganId,
        private ?int $userId,
    ) {}

    public function collection(): Collection
    {
        return app(PersembahanReportService::class)
            ->yearly($this->tahun, $this->wilayahId, $this->lingkunganId, $this->userId)
            ->values()
            ->map(function ($row, $i) {
                $line = [$i + 1, $row['family']->kode_keluarga, $row['family']->nama_kepala_keluarga];

                foreach ($row['per_bulan'] as $nominal) {
                    $line[] = $nominal;
                }

                $line[] = $row['total'];

                return $line;
            });
    }

    public function headings(): array
    {
        return array_merge(
            ['#', 'Kode Keluarga', 'Nama Kepala Keluarga'],
            array_values(Transaction::MONTHS),
            ['Total'],
        );
    }

    public function title(): string
    {
        return 'Rekap Tahunan '.$this->tahun;
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
