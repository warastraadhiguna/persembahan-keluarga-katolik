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

                    <div x-data="{ scanning: false, scanError: null }" class="mt-3">
                        <input x-ref="camInput" type="file" accept="image/*" capture="environment" class="hidden"
                            @change="
                                const file = $event.target.files[0];
                                if (!file) return;
                                scanning = true; scanError = null;
                                window.__scanQrFromFile(file)
                                    .then(text => { scanning = false; $wire.handleQrScanned(text); })
                                    .catch(() => { scanning = false; scanError = 'QR tidak terbaca, coba foto ulang lebih dekat dan jelas.'; });
                                $event.target.value = '';
                            ">

                        <button type="button" @click="scanError = null; $refs.camInput.click()" :disabled="scanning"
                            class="w-full inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                            <template x-if="!scanning">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </template>
                            <template x-if="scanning">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </template>
                            <span x-text="scanning ? 'Membaca QR...' : 'Scan dengan Kamera HP'"></span>
                        </button>

                        <p x-show="scanError" x-text="scanError"
                            class="mt-2 text-xs text-red-500 text-center"></p>
                    </div>

                    <div class="relative my-5">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                        <div class="relative flex justify-center"><span class="bg-white px-3 text-sm text-gray-400">atau cari manual</span></div>
                    </div>

                    <div class="flex items-center gap-3 border border-gray-200 rounded-xl px-4 py-3.5 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500">
                        <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input wire:model.live.debounce.300ms="manualSearch" type="text"
                            placeholder="Cari nama, kode keluarga, atau lingkungan..."
                            class="flex-1 text-base bg-transparent outline-none placeholder-gray-400">
                    </div>

                    @if ($manualSearch)
                        <div class="mt-2 border border-gray-100 rounded-xl divide-y divide-gray-50 max-h-80 overflow-y-auto">
                            @forelse ($this->manualResults as $result)
                                <button wire:click="selectFamilyManual({{ $result->id }})" type="button"
                                    class="w-full text-left px-4 py-3.5 hover:bg-gray-50 transition-colors flex items-center justify-between gap-2">
                                    <div>
                                        <p class="text-base font-medium text-gray-800">{{ $result->nama_kepala_keluarga }}</p>
                                        <p class="text-sm text-gray-400">{{ $result->kode_keluarga }} &bull; {{ $result->lingkungan?->nama ?: '-' }}</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            @empty
                                <p class="px-4 py-4 text-base text-gray-400">Tidak ditemukan.</p>
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-700 mb-5">Input Persembahan</h3>

                    <div class="grid grid-cols-3 gap-3 mb-5">
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-1.5">Bulan</label>
                            <select wire:model.live="bulan"
                                class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach ($this->monthOptions as $num => $label)
                                    <option value="{{ $num }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-1.5">Tanggal</label>
                            <select wire:model="tanggal"
                                class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @for ($d = 1; $d <= $this->daysInMonth; $d++)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-1.5">Tahun</label>
                            <input wire:model.live="tahun" type="number" min="2000" max="2100"
                                class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="mb-5"
                        x-data="{
                            display: '',
                            format(val) {
                                let digits = String(val).replace(/\D/g, '');
                                this.display = digits === '' ? '' : new Intl.NumberFormat('id-ID').format(digits);
                                $wire.set('nominal', digits, false);
                            }
                        }"
                        x-init="display = $wire.nominal ? new Intl.NumberFormat('id-ID').format($wire.nominal) : ''; $nextTick(() => $refs.nominalInput.focus())">
                        <label class="block text-base font-medium text-gray-700 mb-1.5">Nominal (Rp)</label>

                        @if ($this->nominalPresets->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($this->nominalPresets as $preset)
                                    <button type="button"
                                        @click="format('{{ $preset->nominal }}'); $nextTick(() => $refs.nominalInput.focus())"
                                        class="px-4 py-2 text-sm font-semibold rounded-xl border-2 border-primary-300 text-primary-700 bg-primary-50 hover:bg-primary-100 active:bg-primary-200 transition-colors">
                                        {{ $preset->label }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-2 border rounded-xl px-4 py-3 focus-within:ring-2 focus-within:ring-primary-500 {{ $errors->has('nominal') ? 'border-red-300' : 'border-gray-200 focus-within:border-primary-500' }}">
                            <span class="text-lg font-medium text-gray-400 shrink-0">Rp</span>
                            <input x-ref="nominalInput" type="text" inputmode="numeric" placeholder="0"
                                x-model="display" @input="format($event.target.value)"
                                class="flex-1 min-w-0 text-xl font-semibold bg-transparent outline-none placeholder-gray-300">
                        </div>
                        @error('nominal') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="block text-base font-medium text-gray-700 mb-1.5">Catatan (opsional)</label>
                        <textarea wire:model="catatan" rows="2" placeholder="Catatan tambahan..."
                            class="w-full text-base border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white text-base font-semibold px-4 py-4 rounded-xl transition-colors disabled:opacity-50">
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


</div>
