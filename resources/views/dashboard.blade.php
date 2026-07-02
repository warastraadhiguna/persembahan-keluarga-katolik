<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Dashboard</h1>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{-- Total Keluarga --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Keluarga</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalKeluarga) }}</p>
            </div>
        </div>

        {{-- Persembahan Bulan Ini --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Persembahan {{ now()->translatedFormat('F Y') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">Rp {{ number_format((float) $persembahanBulanIni, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Keluarga Sudah Bayar --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Sudah Bayar Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($keluargaSudahBayar) }}
                    <span class="text-sm font-normal text-gray-400">/ {{ number_format($totalKeluarga) }}</span>
                </p>
            </div>
        </div>

        {{-- Transaksi Terakhir --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Data Terakhir Diinput</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $laporanTerakhir }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-700 mb-1">Selamat Datang</h2>
        <p class="text-gray-500 text-sm">
            Sistem Pencatatan Persembahan{{ $namaGereja ? ' ' . $namaGereja : '' }}.
            Gunakan menu di sebelah kiri untuk mengelola data keluarga, persembahan, dan laporan.
        </p>
    </div>
</x-main-layout>
