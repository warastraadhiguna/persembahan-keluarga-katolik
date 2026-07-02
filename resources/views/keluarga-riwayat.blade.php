<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Riwayat Persembahan Keluarga</h1>
    </x-slot>

    <a href="{{ route('keluarga') }}" wire:navigate
        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Data Keluarga
    </a>

    {{-- Kartu info keluarga + tombol unduh --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="font-mono text-xs text-primary-700 font-semibold">{{ $family->kode_keluarga }}</p>
                <p class="text-lg font-semibold text-gray-800">{{ $family->nama_kepala_keluarga }}</p>
                <p class="text-sm text-gray-500">
                    {{ $family->lingkungan?->nama ?: '-' }}
                    <span class="text-gray-300">/</span>
                    {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
                </p>
            </div>

            <div class="flex gap-2 shrink-0">
                <a href="{{ route('keluarga.riwayat.excel', $family) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor Excel
                </a>
                <a href="{{ route('keluarga.riwayat.pdf', $family) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Ekspor PDF
                </a>
            </div>
        </div>
    </div>

    <livewire:family-transaction-history :family="$family" />
</x-main-layout>
