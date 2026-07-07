<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Backup Database</h1>
    </x-slot>

    <div class="max-w-xl mx-auto space-y-5">

        {{-- Info --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Backup Seluruh Database</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Mengunduh semua data termasuk keluarga, transaksi persembahan, pengguna, dan konfigurasi dalam format SQL.
                        File dapat di-restore langsung ke MySQL atau phpMyAdmin.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Semua tabel & data
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Kompatibel MySQL 5.7+
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Dicatat di Log Audit
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol download --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Pilih Format Unduhan</h3>

            <a href="{{ route('backup.download') }}"
                class="flex items-center gap-4 p-4 rounded-xl border-2 border-primary-100 hover:border-primary-400 hover:bg-primary-50 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-primary-100 group-hover:bg-primary-200 flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Download SQL</p>
                    <p class="text-xs text-gray-500 mt-0.5">Plain text, langsung bisa dibuka & di-restore</p>
                </div>
                <span class="text-xs font-mono text-gray-400">.sql</span>
            </a>

            <a href="{{ route('backup.download.gz') }}"
                class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-gray-300 hover:bg-gray-50 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-gray-100 group-hover:bg-gray-200 flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Download SQL (Compressed)</p>
                    <p class="text-xs text-gray-500 mt-0.5">Ukuran lebih kecil, cocok untuk simpan / kirim via email</p>
                </div>
                <span class="text-xs font-mono text-gray-400">.sql.gz</span>
            </a>

            <a href="{{ route('backup.download.excel') }}"
                class="flex items-center gap-4 p-4 rounded-xl border-2 border-green-100 hover:border-green-400 hover:bg-green-50 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-green-100 group-hover:bg-green-200 flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M3 14h18M10 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Download Excel</p>
                    <p class="text-xs text-gray-500 mt-0.5">Data keluarga, transaksi, wilayah, lingkungan & pengguna — tiap sheet terpisah. Mudah dibuka di Excel / Sheets.</p>
                </div>
                <span class="text-xs font-mono text-gray-400">.xlsx</span>
            </a>
        </div>

        {{-- Peringatan --}}
        <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-amber-50 border border-amber-100 text-amber-700 text-xs">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Simpan file backup di tempat yang aman. File ini berisi seluruh data termasuk informasi sensitif pengguna.</span>
        </div>

    </div>
</x-main-layout>
