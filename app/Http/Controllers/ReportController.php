<?php

namespace App\Http\Controllers;

use App\Exports\FamilyTransactionExport;
use App\Exports\MonthlyReportExport;
use App\Exports\YearlyReportExport;
use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\PersembahanReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private PersembahanReportService $reports) {}

    public function monthlyExcel(Request $request)
    {
        [$dateFrom, $dateTo, $wilayahId, $lingkunganId, $userId, $statusFilter] = $this->monthlyFilters($request);

        return Excel::download(
            new MonthlyReportExport($dateFrom, $dateTo, $wilayahId, $lingkunganId, $userId, $statusFilter),
            "rekap-bulanan-{$dateFrom}-sd-{$dateTo}.xlsx"
        );
    }

    public function monthlyPdf(Request $request)
    {
        [$dateFrom, $dateTo, $wilayahId, $lingkunganId, $userId, $statusFilter] = $this->monthlyFilters($request);

        $rows        = $this->reports->monthly($dateFrom, $dateTo, $wilayahId, $lingkunganId, $userId, $statusFilter);
        $petugasName = $userId ? User::find($userId)?->name : null;

        $pdf = Pdf::loadView('reports.monthly-pdf', [
            'rows'         => $rows,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'statusFilter' => $statusFilter,
            'wilayah'      => $wilayahId ? Wilayah::find($wilayahId)?->nama : null,
            'lingkungan'   => $lingkunganId ? Lingkungan::find($lingkunganId)?->nama : null,
            'petugasName'  => $petugasName,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("rekap-bulanan-{$dateFrom}-sd-{$dateTo}.pdf");
    }

    public function yearlyExcel(Request $request)
    {
        [$tahun, $wilayahId, $lingkunganId, $userId] = $this->yearlyFilters($request);

        return Excel::download(
            new YearlyReportExport($tahun, $wilayahId, $lingkunganId, $userId),
            "rekap-tahunan-{$tahun}.xlsx"
        );
    }

    public function yearlyPdf(Request $request)
    {
        [$tahun, $wilayahId, $lingkunganId, $userId] = $this->yearlyFilters($request);

        $rows = $this->reports->yearly($tahun, $wilayahId, $lingkunganId, $userId);

        $perBulanTotal = array_fill(1, 12, 0.0);
        foreach ($rows as $row) {
            foreach ($row['per_bulan'] as $bulan => $nominal) {
                $perBulanTotal[$bulan] += $nominal;
            }
        }

        $petugasName = $userId ? User::find($userId)?->name : null;

        $pdf = Pdf::loadView('reports.yearly-pdf', [
            'rows' => $rows, 'tahun' => $tahun,
            'perBulanTotal' => $perBulanTotal, 'grandTotal' => array_sum($perBulanTotal),
            'wilayah'    => $wilayahId ? Wilayah::find($wilayahId)?->nama : null,
            'lingkungan' => $lingkunganId ? Lingkungan::find($lingkunganId)?->nama : null,
            'petugasName' => $petugasName,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("rekap-tahunan-{$tahun}.pdf");
    }

    public function keluargaList()
    {
        return view('laporan-keluarga');
    }

    public function keluargaRiwayat(Family $family)
    {
        $family->loadMissing('lingkungan.wilayah');

        return view('laporan-keluarga-riwayat', compact('family'));
    }

    public function keluargaExcel(Family $family)
    {
        $family->loadMissing('lingkungan.wilayah');

        $slug = str($family->nama_kepala_keluarga)->slug('-')->limit(30);

        return Excel::download(
            new FamilyTransactionExport($family),
            "riwayat-{$family->kode_keluarga}-{$slug}.xlsx"
        );
    }

    public function keluargaPdf(Family $family)
    {
        $family->loadMissing('lingkungan.wilayah');

        $transactions = $family->transactions()
            ->with(['petugas:id,name'])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        $active       = $transactions->where('is_void', false);
        $totalNominal = (float) $active->sum('nominal');
        $monthsPaid   = $active->unique(fn ($t) => $t->tahun.'-'.$t->bulan)->count();

        $yearlyGrid = [];
        foreach ($active as $t) {
            $yearlyGrid[$t->tahun][$t->bulan] = ($yearlyGrid[$t->tahun][$t->bulan] ?? 0) + (float) $t->nominal;
        }
        krsort($yearlyGrid);

        $slug = str($family->nama_kepala_keluarga)->slug('-')->limit(30);

        $pdf = Pdf::loadView('reports.family-transaction-pdf', compact(
            'family', 'transactions', 'yearlyGrid', 'totalNominal', 'monthsPaid'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("riwayat-{$family->kode_keluarga}-{$slug}.pdf");
    }

    private function monthlyFilters(Request $request): array
    {
        return [
            $request->query('date_from', now()->startOfMonth()->format('Y-m-d')),
            $request->query('date_to',   now()->format('Y-m-d')),
            $request->query('wilayah_id') ? (int) $request->query('wilayah_id') : null,
            $request->query('lingkungan_id') ? (int) $request->query('lingkungan_id') : null,
            $request->query('user_id') ? (int) $request->query('user_id') : null,
            $request->query('status_filter', ''),
        ];
    }

    private function yearlyFilters(Request $request): array
    {
        return [
            (int) $request->query('tahun', now()->year),
            $request->query('wilayah_id') ? (int) $request->query('wilayah_id') : null,
            $request->query('lingkungan_id') ? (int) $request->query('lingkungan_id') : null,
            $request->query('user_id') ? (int) $request->query('user_id') : null,
        ];
    }
}
