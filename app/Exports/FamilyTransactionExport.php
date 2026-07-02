<?php

namespace App\Exports;

use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FamilyTransactionExport implements WithEvents, WithTitle
{
    private Collection $transactions;
    private array $yearlyGrid;
    private int $monthsPaid;
    private float $totalNominal;

    public function __construct(private Family $family)
    {
        $this->transactions = $family->transactions()
            ->with(['petugas:id,name'])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $active = $this->transactions->where('is_void', false);

        $this->totalNominal = (float) $active->sum('nominal');
        $this->monthsPaid   = $active->unique(fn ($t) => $t->tahun.'-'.$t->bulan)->count();

        $grid = [];
        foreach ($active as $t) {
            $grid[$t->tahun][$t->bulan] = ($grid[$t->tahun][$t->bulan] ?? 0) + (float) $t->nominal;
        }
        krsort($grid);
        $this->yearlyGrid = $grid;
    }

    public function title(): string
    {
        return 'Riwayat '.$this->family->kode_keluarga;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet  = $event->sheet->getDelegate();
                $church = ChurchSetting::current();

                // ── Kolom widths ─────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(10);   // No / Tahun
                foreach (range('B', 'N') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(10);
                }
                $sheet->getColumnDimension('C')->setWidth(14);   // Bulan/Tahun
                $sheet->getColumnDimension('D')->setWidth(16);   // Nominal
                $sheet->getColumnDimension('E')->setWidth(18);   // Petugas
                $sheet->getColumnDimension('F')->setWidth(13);   // Status
                $sheet->getColumnDimension('G')->setWidth(28);   // Keterangan
                $sheet->getColumnDimension('N')->setWidth(14);   // Total

                $lastCol = 'N';
                $row     = 1;

                // ── KOP ──────────────────────────────────────
                if ($church->nama) {
                    $sheet->setCellValue("A{$row}", strtoupper($church->nama));
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;
                }
                if ($church->alamat) {
                    $sheet->setCellValue("A{$row}", $church->alamat);
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setSize(9);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;
                }
                $contact = trim(
                    ($church->telepon ? 'Telp: '.$church->telepon : '')
                    .($church->telepon && $church->email ? '   |   ' : '')
                    .($church->email ? 'Email: '.$church->email : '')
                );
                if ($contact) {
                    $sheet->setCellValue("A{$row}", $contact);
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setSize(9);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                // Border bawah kop
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_MEDIUM);
                $row++;

                // ── Judul ────────────────────────────────────
                $row++; // spacer
                $sheet->setCellValue("A{$row}", 'Laporan Persembahan Keluarga');
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;

                $sheet->setCellValue("A{$row}", $this->family->nama_kepala_keluarga);
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;

                $infoLine = 'Kode: '.$this->family->kode_keluarga
                    .'   |   Lingkungan: '.($this->family->lingkungan?->nama ?: '-')
                    .'   |   Wilayah: '.($this->family->lingkungan?->wilayah?->nama ?: '-');
                $sheet->setCellValue("A{$row}", $infoLine);
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(9);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('6b7280');
                $row++;

                $sheet->setCellValue("A{$row}", 'Dicetak: '.now()->format('d/m/Y H:i'));
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setSize(8);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('9ca3af');
                $row++;

                // ── Summary stats ────────────────────────────
                $row++; // spacer
                $sheet->setCellValue("A{$row}", 'Bulan Lunas');
                $sheet->setCellValue("B{$row}", $this->monthsPaid);
                $sheet->setCellValue("D{$row}", 'Total Nominal');
                $sheet->setCellValue("E{$row}", $this->totalNominal);
                $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
                $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $row++;

                // ── Section: Track Record ─────────────────────
                if (! empty($this->yearlyGrid)) {
                    $row++; // spacer
                    $sheet->setCellValue("A{$row}", 'TRACK RECORD PERSEMBAHAN');
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
                    $sheet->getStyle("A{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('1d4ed8');
                    $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('ffffff');
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;

                    // Header bulan
                    $months = Transaction::MONTHS;
                    $sheet->setCellValue("A{$row}", 'Tahun');
                    $colIdx = 'B';
                    foreach ($months as $label) {
                        $sheet->setCellValue("{$colIdx}{$row}", substr($label, 0, 3));
                        $colIdx++;
                    }
                    $sheet->setCellValue("{$lastCol}{$row}", 'Total');
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('dbeafe');
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $row++;

                    foreach ($this->yearlyGrid as $tahun => $monthData) {
                        $sheet->setCellValue("A{$row}", $tahun);
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true);

                        $colIdx = 'B';
                        $yearTotal = 0;
                        for ($m = 1; $m <= 12; $m++) {
                            if (isset($monthData[$m])) {
                                $sheet->setCellValue("{$colIdx}{$row}", $monthData[$m]);
                                $sheet->getStyle("{$colIdx}{$row}")->getNumberFormat()
                                    ->setFormatCode('#,##0');
                                $sheet->getStyle("{$colIdx}{$row}")->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setRGB('dcfce7');
                                $yearTotal += $monthData[$m];
                            } else {
                                $sheet->setCellValue("{$colIdx}{$row}", '-');
                                $sheet->getStyle("{$colIdx}{$row}")->getFont()->getColor()->setRGB('d1d5db');
                            }
                            $sheet->getStyle("{$colIdx}{$row}")->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $colIdx++;
                        }

                        $sheet->setCellValue("{$lastCol}{$row}", $yearTotal);
                        $sheet->getStyle("{$lastCol}{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                        $sheet->getStyle("{$lastCol}{$row}")->getFont()->setBold(true);

                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getBorders()->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN);

                        $row++;
                    }
                }

                // ── Section: Detail Transaksi ─────────────────
                $row++; // spacer
                $sheet->setCellValue("A{$row}", 'DETAIL TRANSAKSI');
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle("A{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('1d4ed8');
                $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('ffffff');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;

                // Header detail
                $detailHeaders = ['#', 'Tanggal', 'Bulan / Tahun', 'Nominal', 'Petugas', 'Status', 'Keterangan'];
                $detailCols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                foreach ($detailHeaders as $i => $h) {
                    $sheet->setCellValue("{$detailCols[$i]}{$row}", $h);
                }
                $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:G{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('dbeafe');
                $sheet->getStyle("A{$row}:G{$row}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $row++;

                $grandTotal     = 0;
                $detailStartRow = $row;

                foreach ($this->transactions as $i => $t) {
                    $tanggal = $t->tanggal
                        ? str_pad($t->tanggal, 2, '0', STR_PAD_LEFT).'/'
                            .substr(Transaction::monthLabel($t->bulan), 0, 3).'/'.$t->tahun
                        : $t->created_at->format('d/m/Y');

                    $sheet->setCellValue("A{$row}", $i + 1);
                    $sheet->setCellValue("B{$row}", $tanggal);
                    $sheet->setCellValue("C{$row}", Transaction::monthLabel($t->bulan).' '.$t->tahun);
                    $sheet->setCellValue("D{$row}", (float) $t->nominal);
                    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                    $sheet->setCellValue("E{$row}", $t->petugas?->name ?: '-');
                    $sheet->setCellValue("F{$row}", $t->is_void ? 'Dibatalkan' : 'Lunas');
                    $sheet->setCellValue("G{$row}", $t->is_void
                        ? ($t->void_reason ?: '')
                        : ($t->catatan ?: ''));

                    if ($t->is_void) {
                        $sheet->getStyle("A{$row}:G{$row}")->getFont()->getColor()->setRGB('9ca3af');
                    } else {
                        $grandTotal += (float) $t->nominal;
                    }

                    $sheet->getStyle("A{$row}:G{$row}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    $row++;
                }

                // Total row
                if ($this->transactions->isNotEmpty()) {
                    $sheet->setCellValue("C{$row}", 'Total');
                    $sheet->setCellValue("D{$row}", $grandTotal);
                    $sheet->getStyle("C{$row}:D{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                    $sheet->getStyle("A{$row}:G{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('f3f4f6');
                    $sheet->getStyle("A{$row}:G{$row}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
            },
        ];
    }
}
