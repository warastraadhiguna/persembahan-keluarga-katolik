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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <p class="font-mono text-xs text-primary-700 font-semibold">{{ $family->kode_keluarga }}</p>
        <p class="text-lg font-semibold text-gray-800">{{ $family->nama_kepala_keluarga }}</p>
        <p class="text-sm text-gray-500">
            {{ $family->lingkungan?->nama ?: '-' }} <span class="text-gray-300">/</span> {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
        </p>
    </div>

    <livewire:family-transaction-history :family="$family" />
</x-main-layout>
