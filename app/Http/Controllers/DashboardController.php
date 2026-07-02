<?php

namespace App\Http\Controllers;

use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $bulan = now()->month;
        $tahun = now()->year;

        $totalKeluarga = Family::count();

        $persembahanBulanIni = Transaction::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('is_void', false)
            ->sum('nominal');

        $keluargaSudahBayar = Transaction::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('is_void', false)
            ->distinct('family_id')
            ->count('family_id');

        $transaksiTerakhir = Transaction::where('is_void', false)
            ->latest()
            ->first();

        $laporanTerakhir = $transaksiTerakhir
            ? Transaction::monthLabel($transaksiTerakhir->bulan) . ' ' . $transaksiTerakhir->tahun
            : '-';

        $namaGereja = ChurchSetting::current()->nama;

        return view('dashboard', compact(
            'totalKeluarga',
            'persembahanBulanIni',
            'keluargaSudahBayar',
            'laporanTerakhir',
            'namaGereja',
        ));
    }
}
