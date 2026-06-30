<?php

use App\Http\Controllers\FamilyQrController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // Master data
    Route::view('pengguna', 'pengguna')
        ->middleware('role:pengguna')
        ->name('pengguna');

    Route::view('wilayah-lingkungan', 'wilayah-lingkungan')
        ->middleware('role:wilayah-lingkungan')
        ->name('wilayah-lingkungan');

    Route::view('role-permission', 'role-permission')
        ->middleware('role:role-permission')
        ->name('role-permission');

    // Manajemen
    Route::view('keluarga', 'keluarga')
        ->middleware('role:keluarga')
        ->name('keluarga');

    Route::middleware('role:keluarga')->group(function () {
        Route::get('keluarga/cetak', [FamilyQrController::class, 'cetak'])
            ->name('keluarga.cetak');

        Route::get('keluarga/{family}/qr', [FamilyQrController::class, 'show'])
            ->name('keluarga.qr');
    });

    Route::view('persembahan', 'persembahan')
        ->middleware('role:persembahan')
        ->name('persembahan');

    Route::middleware('role:laporan')->group(function () {
        Route::view('laporan/bulanan', 'laporan-bulanan')->name('laporan.bulanan');
        Route::view('laporan/tahunan', 'laporan-tahunan')->name('laporan.tahunan');

        Route::get('laporan/bulanan/excel', [ReportController::class, 'monthlyExcel'])->name('laporan.bulanan.excel');
        Route::get('laporan/bulanan/pdf', [ReportController::class, 'monthlyPdf'])->name('laporan.bulanan.pdf');
        Route::get('laporan/tahunan/excel', [ReportController::class, 'yearlyExcel'])->name('laporan.tahunan.excel');
        Route::get('laporan/tahunan/pdf', [ReportController::class, 'yearlyPdf'])->name('laporan.tahunan.pdf');
    });

    Route::view('audit', 'audit-log')
        ->middleware('role:audit')
        ->name('audit.log');
});

require __DIR__.'/auth.php';
