<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Exports\YearlyReportExport;
use App\Models\Lingkungan;
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
        [$bulan, $tahun, $wilayahId, $lingkunganId, $userId] = $this->monthlyFilters($request);

        return Excel::download(
            new MonthlyReportExport($bulan, $tahun, $wilayahId, $lingkunganId, $userId),
            "rekap-bulanan-{$tahun}-{$bulan}.xlsx"
        );
    }

    public function monthlyPdf(Request $request)
    {
        [$bulan, $tahun, $wilayahId, $lingkunganId, $userId] = $this->monthlyFilters($request);

        $rows = $this->reports->monthly($bulan, $tahun, $wilayahId, $lingkunganId, $userId);
        $petugasName = $userId ? User::find($userId)?->name : null;

        $pdf = Pdf::loadView('reports.monthly-pdf', [
            'rows' => $rows, 'bulan' => $bulan, 'tahun' => $tahun,
            'wilayah'    => $wilayahId ? Wilayah::find($wilayahId)?->nama : null,
            'lingkungan' => $lingkunganId ? Lingkungan::find($lingkunganId)?->nama : null,
            'petugasName' => $petugasName,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("rekap-bulanan-{$tahun}-{$bulan}.pdf");
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

    private function monthlyFilters(Request $request): array
    {
        return [
            (int) $request->query('bulan', now()->month),
            (int) $request->query('tahun', now()->year),
            $request->query('wilayah_id') ? (int) $request->query('wilayah_id') : null,
            $request->query('lingkungan_id') ? (int) $request->query('lingkungan_id') : null,
            $request->query('user_id') ? (int) $request->query('user_id') : null,
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
