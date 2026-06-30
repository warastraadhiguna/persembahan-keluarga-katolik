<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Detail QR Keluarga</h1>
    </x-slot>

    <a href="{{ route('keluarga') }}" wire:navigate
        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Data Keluarga
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center flex flex-col items-center justify-center">
            <div class="flex justify-center mb-4">
                <div class="p-3 bg-white border border-gray-200 rounded-lg inline-block">
                    {!! QrCode::size(220)->generate($family->qr_token) !!}
                </div>
            </div>

            <p class="font-mono text-sm text-primary-700 font-semibold tracking-wide">{{ $family->kode_keluarga }}</p>
            <p class="text-lg font-semibold text-gray-800 mt-1">{{ $family->nama_kepala_keluarga }}</p>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $family->lingkungan?->nama ?: '-' }} <span class="text-gray-300">/</span> {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
            </p>

            <span class="inline-flex items-center gap-1.5 mt-3 px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $family->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $family->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                {{ $family->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        {{-- Cetak satuan: pilih posisi pada lembar stiker --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col justify-center" x-data="{ paper: '{{ $printSetting->paper }}' }">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Cetak Stiker Keluarga Ini</h3>
            <form action="{{ route('keluarga.cetak') }}" method="GET" target="_blank" class="space-y-3">
                <input type="hidden" name="ids" value="{{ $family->id }}">

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Baris</label>
                        <input type="number" name="rows" value="{{ $printSetting->rows }}" min="1" max="20"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kolom</label>
                        <input type="number" name="cols" value="{{ $printSetting->cols }}" min="1" max="10"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Posisi Mulai</label>
                        <input type="number" name="start" value="1" min="1"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Ukuran Kertas</label>
                    <select name="paper" x-model="paper"
                        class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="a4">A4 (210x297mm)</option>
                        <option value="f4">F4 / Folio (215x330mm)</option>
                        <option value="letter">Letter (215.9x279.4mm)</option>
                        <option value="custom">Custom...</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2" x-show="paper === 'custom'">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Lebar (mm)</label>
                        <input type="number" name="paper_width" value="{{ $printSetting->paper_width }}" min="50" max="500" step="0.1"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tinggi (mm)</label>
                        <input type="number" name="paper_height" value="{{ $printSetting->paper_height }}" min="50" max="500" step="0.1"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Margin Kertas (mm)</label>
                        <input type="number" name="margin" value="{{ $printSetting->margin }}" min="0" max="50" step="0.5"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Jarak Antar Stiker (mm)</label>
                        <input type="number" name="gap" value="{{ $printSetting->gap }}" min="0" max="20" step="0.5"
                            class="w-full text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <p class="text-xs text-gray-400">
                    Posisi 1 = pojok kiri atas. Gunakan ini untuk mencetak di sisa lembar stiker yang sudah terpakai sebagian.
                </p>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Buka Pratinjau Cetak
                </button>
            </form>
        </div>
    </div>
</x-main-layout>
