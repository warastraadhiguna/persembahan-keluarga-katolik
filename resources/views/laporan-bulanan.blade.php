<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Rekap Bulanan</h1>
    </x-slot>

    <div class="mb-5 flex gap-2">
        <a href="{{ route('laporan.bulanan') }}" wire:navigate
            class="px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('laporan.bulanan') ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Rekap Bulanan
        </a>
        <a href="{{ route('laporan.tahunan') }}" wire:navigate
            class="px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('laporan.tahunan') ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Rekap Tahunan
        </a>
    </div>

    <livewire:monthly-report />
</x-main-layout>
