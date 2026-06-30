<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Dashboard</h1>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Total Umat</p>
            <p class="text-2xl font-bold text-primary-600">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Persembahan Bulan Ini</p>
            <p class="text-2xl font-bold text-primary-600">Rp 0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Misa Bulan Ini</p>
            <p class="text-2xl font-bold text-primary-600">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <p class="text-sm text-gray-500 mb-1">Laporan Terakhir</p>
            <p class="text-2xl font-bold text-primary-600">-</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-700 mb-3">Selamat Datang</h2>
        <p class="text-gray-500 text-sm">
            Sistem Pencatatan Persembahan Gereja Katolik siap digunakan.
            Modul umat, persembahan, dan laporan akan tersedia segera.
        </p>
    </div>
</x-main-layout>
