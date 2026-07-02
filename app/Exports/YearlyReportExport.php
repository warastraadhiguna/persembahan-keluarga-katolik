<?php

namespace App\Exports;

use App\Models\ChurchSetting;
use App\Models\Transaction;
use App\Services\PersembahanReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class YearlyReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
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
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // 3 + 12 months + 1 total = 16 columns → P
                $lastCol = 'P';

                // Insert 6 rows at top for kop block
                $sheet->insertNewRowBefore(1, 6);

                $church = ChurchSetting::current();

                // Row 1: church name
                $sheet->setCellValue('A1', strtoupper($church->nama ?: ''));
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Row 2: address
                if ($church->alamat) {
                    $sheet->setCellValue('A2', $church->alamat);
                    $sheet->mergeCells("A2:{$lastCol}2");
                    $sheet->getStyle('A2')->getFont()->setSize(9);
                    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                }

                // Row 3: telepon / email
                $contact = trim(
                    ($church->telepon ? 'Telp: '.$church->telepon : '')
                    . ($church->telepon && $church->email ? '   |   ' : '')
                    . ($church->email ? 'Email: '.$church->email : '')
                );
                if ($contact) {
                    $sheet->setCellValue('A3', $contact);
                    $sheet->mergeCells("A3:{$lastCol}3");
                    $sheet->getStyle('A3')->getFont()->setSize(9);
                    $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
                }

                // Border bottom kop (row 3)
                $sheet->getStyle("A3:{$lastCol}3")
                    ->getBorders()->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

                // Row 5: report title
                $sheet->setCellValue('A5', 'Rekap Persembahan Tahunan');
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

                // Row 6: year
                $sheet->setCellValue('A6', 'Tahun '.$this->tahun);
                $sheet->mergeCells("A6:{$lastCol}6");
                $sheet->getStyle('A6')->getFont()->setSize(10)->setColor(
                    (new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB('6b7280')
                );
                $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');

                // Heading row (now row 7)
                $sheet->getStyle("A7:{$lastCol}7")->getFont()->setBold(true);
                $sheet->getStyle("A7:{$lastCol}7")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('f3f4f6');

                // Row spacing
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(4);
            },
        ];
    }
}
