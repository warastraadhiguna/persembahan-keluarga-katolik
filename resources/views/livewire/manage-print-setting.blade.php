<div x-data="{ paper: @entangle('paper') }">

    @if ($saved)
        <div class="mb-5 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Pengaturan cetak berhasil disimpan.
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">

        {{-- Grid Stiker --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Grid Stiker
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Baris</label>
                    <input wire:model="rows" type="number" min="1" max="20"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('rows') border-red-300 @enderror">
                    @error('rows') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kolom</label>
                    <input wire:model="cols" type="number" min="1" max="10"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('cols') border-red-300 @enderror">
                    @error('cols') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stiker per lembar</label>
                    <div class="text-sm border border-gray-100 bg-gray-50 rounded-lg px-3 py-2 text-gray-500">
                        {{ $rows * $cols }} stiker
                    </div>
                </div>
            </div>
        </div>

        {{-- Ukuran Kertas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ukuran Kertas
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Kertas</label>
                    <select wire:model.live="paper" x-model="paper"
                        class="w-full sm:w-64 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="a4">A4 (210 × 297 mm)</option>
                        <option value="f4">F4 / Folio (215 × 330 mm)</option>
                        <option value="letter">Letter (215.9 × 279.4 mm)</option>
                        <option value="custom">Custom...</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3" x-show="paper === 'custom'">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lebar (mm)</label>
                        <input wire:model="paperWidth" type="number" min="50" max="500" step="0.1"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('paperWidth') border-red-300 @enderror">
                        @error('paperWidth') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tinggi (mm)</label>
                        <input wire:model="paperHeight" type="number" min="50" max="500" step="0.1"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('paperHeight') border-red-300 @enderror">
                        @error('paperHeight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Spasi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
                Spasi & Margin
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Margin Kertas (mm)</label>
                    <input wire:model="margin" type="number" min="0" max="50" step="0.5"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('margin') border-red-300 @enderror">
                    @error('margin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">Jarak pinggir kertas ke stiker terluar</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jarak Antar Stiker (mm)</label>
                    <input wire:model="gap" type="number" min="0" max="20" step="0.5"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('gap') border-red-300 @enderror">
                    @error('gap') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">0 = stiker rapat tanpa celah</p>
                </div>
            </div>
        </div>

        {{-- Ukuran QR --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0h-2m0 0H8m12-4v.01M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m-4 14h2a2 2 0 002-2v-2M8 8h2v2H8V8zm6 0h2v2h-2V8zm-6 6h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                </svg>
                Ukuran QR Code
            </h3>
            <div x-data="{ qrSize: @entangle('qrSize') }">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gray-600">Ukuran QR dalam Stiker</label>
                    <span class="text-sm font-semibold text-primary-600" x-text="qrSize + '%'"></span>
                </div>
                <input type="range" min="20" max="90" step="5"
                    x-model="qrSize"
                    @change="$wire.set('qrSize', parseInt(qrSize))"
                    class="w-full accent-primary-600">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>Kecil (20%)</span>
                    <span>Besar (90%)</span>
                </div>
                <p class="mt-2 text-xs text-gray-400">Persentase lebar QR terhadap lebar stiker. Sisanya untuk teks nama.</p>
            </div>
        </div>

        {{-- Tombol Simpan --}}
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <span wire:loading wire:target="save">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>

    </form>
</div>
