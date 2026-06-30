<div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ============ KOLOM UTAMA: SCAN / FORM ============ --}}
        <div class="lg:col-span-2 space-y-5">

            @if (! $family)
                {{-- ===== MODE SCAN / CARI ===== --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-base font-semibold text-gray-800 mb-1">Scan Kartu Keluarga</h2>
                    <p class="text-xs text-gray-500 mb-4">Arahkan scanner USB ke barcode/QR, atau gunakan kamera HP.</p>

                    <div x-data x-init="$el.focus()" class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0h-2m0 0H8m12-4v.01M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m-4 14h2a2 2 0 002-2v-2M8 8h2v2H8V8zm6 0h2v2h-2V8zm-6 6h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                        </svg>
                        <input wire:model="qrInput" wire:keydown.enter="lookupByToken" type="text" autofocus
                            placeholder="Scan QR keluarga di sini..."
                            class="w-full pl-11 pr-3 py-3 text-base border-2 border-dashed border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('qrInput') border-red-300 @enderror">
                    </div>
                    @error('qrInput') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    <button wire:click="$set('showScanModal', true)"
                        class="mt-3 w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Scan dengan Kamera HP
                    </button>

                    <div class="relative my-5">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                        <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-400">atau cari manual</span></div>
                    </div>

                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input wire:model.live.debounce.300ms="manualSearch" type="text"
                            placeholder="Cari nama, kode keluarga, atau lingkungan..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    @if ($manualSearch)
                        <div class="mt-2 border border-gray-100 rounded-lg divide-y divide-gray-50 max-h-72 overflow-y-auto">
                            @forelse ($this->manualResults as $result)
                                <button wire:click="selectFamilyManual({{ $result->id }})" type="button"
                                    class="w-full text-left px-3 py-2.5 hover:bg-gray-50 transition-colors flex items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $result->nama_kepala_keluarga }}</p>
                                        <p class="text-xs text-gray-400">{{ $result->kode_keluarga }} &bull; {{ $result->lingkungan?->nama ?: '-' }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            @empty
                                <p class="px-3 py-3 text-sm text-gray-400">Tidak ditemukan.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @else
                {{-- ===== KARTU KELUARGA TERPILIH ===== --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-mono text-xs text-primary-700 font-semibold">{{ $family->kode_keluarga }}</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $family->nama_kepala_keluarga }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $family->lingkungan?->nama ?: '-' }} <span class="text-gray-300">/</span> {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
                            </p>
                        </div>
                        <button wire:click="clearFamily" title="Ganti keluarga"
                            class="text-xs text-gray-500 hover:text-red-600 border border-gray-200 hover:border-red-200 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap">
                            Ganti Keluarga
                        </button>
                    </div>
                </div>

                {{-- Peringatan duplikat --}}
                @if ($this->duplicateTransaction)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">Keluarga ini sudah tercatat untuk {{ $this->monthOptions[$bulan] ?? '' }} {{ $tahun }}.</p>
                            <p class="text-xs text-amber-700 mt-0.5">
                                Rp {{ number_format((float) $this->duplicateTransaction->nominal, 0, ',', '.') }}
                                pada {{ $this->duplicateTransaction->created_at->format('d/m/Y H:i') }}.
                                Anda tetap bisa menyimpan sebagai koreksi/tambahan.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Form input nominal --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Input Persembahan</h3>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select wire:model.live="bulan"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach ($this->monthOptions as $num => $label)
                                    <option value="{{ $num }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <input wire:model.live="tahun" type="number" min="2000" max="2100"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                        <input wire:model="nominal" type="number" step="0.01" min="0" placeholder="0"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('nominal') border-red-300 @enderror">
                        @error('nominal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                        <textarea wire:model="catatan" rows="2" placeholder="Catatan tambahan..."
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Simpan Persembahan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- ============ KOLOM SAMPING: RIWAYAT SESI INI ============ --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sticky top-20">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Transaksi Sesi Ini</h3>

                @if (empty($recentTransactions))
                    <p class="text-sm text-gray-400">Belum ada transaksi yang diinput.</p>
                @else
                    <div class="space-y-2 max-h-[28rem] overflow-y-auto">
                        @foreach ($recentTransactions as $t)
                            <div class="border border-gray-100 rounded-lg px-3 py-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $t['nama'] }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $t['kode_keluarga'] }} &bull;
                                            {{ $this->monthOptions[$t['bulan']] ?? $t['bulan'] }} {{ $t['tahun'] }}
                                        </p>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $t['waktu'] }}</span>
                                </div>
                                <p class="text-sm font-semibold text-primary-700 mt-1">
                                    Rp {{ number_format($t['nominal'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ MODAL SCAN KAMERA ============ --}}
    <div x-data="{ show: @entangle('showScanModal') }" x-show="show"
        x-on:qr-scanned.window="$wire.handleQrScanned($event.detail)"
        x-effect="show ? window.__startQrScanner('qr-reader-box') : window.__stopQrScanner()"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
        style="display:none">
        <div @click.outside="show = false" class="bg-white rounded-xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Scan QR dengan Kamera</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <div id="qr-reader-box" class="w-full rounded-lg overflow-hidden bg-gray-900" style="min-height:260px"></div>
                <p class="text-xs text-gray-400 mt-3 text-center">Arahkan kamera ke QR kartu keluarga.</p>
            </div>
        </div>
    </div>

</div>
