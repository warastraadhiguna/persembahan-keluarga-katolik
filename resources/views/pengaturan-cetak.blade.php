<x-main-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-800">Pengaturan Cetak Stiker</h1>
    </x-slot>

    <div class="max-w-2xl">
        <p class="text-sm text-gray-500 mb-5">
            Pengaturan ini menjadi nilai default setiap kali membuka menu cetak stiker QR. Bisa diubah lagi saat cetak jika diperlukan.
        </p>
        <livewire:manage-print-setting />
    </div>
</x-main-layout>
