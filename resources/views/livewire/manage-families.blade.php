<div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <h2 class="text-base font-semibold text-gray-800">Data Keluarga</h2>
                <p class="text-xs text-gray-500 mt-0.5">Kelola data keluarga umat per lingkungan dan wilayah.</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="openImport"
                    class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Impor Excel
                </button>
                <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Keluarga
                </button>
            </div>
        </div>

        {{-- Filter bar --}}
        <div class="mt-4 flex flex-col sm:flex-row gap-2 items-center">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, kode keluarga, atau lingkungan..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            {{-- Searchable Wilayah --}}
            <div x-data="{
                    open: false,
                    search: '',
                    options: [
                        { value: '', label: 'Semua Wilayah' },
                        @foreach ($this->wilayahOptions as $w)
                        { value: '{{ $w->id }}', label: @js($w->nama) },
                        @endforeach
                    ],
                    get selected() { return this.options.find(o => o.value == $wire.filterWilayahId) ?? this.options[0]; },
                    get filtered() {
                        if (!this.search) return this.options;
                        const q = this.search.toLowerCase();
                        return this.options.filter(o => o.label.toLowerCase().includes(q));
                    },
                    pick(opt) { $wire.set('filterWilayahId', opt.value); this.open = false; this.search = ''; }
                }"
                @keydown.escape="open = false"
                @click.outside="open = false"
                class="relative">
                <button type="button" @click="open = !open"
                    class="w-44 flex items-center justify-between gap-2 text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <span class="truncate" x-text="selected.label"></span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="absolute z-50 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg" style="display:none">
                    <div class="p-2 border-b border-gray-100">
                        <input type="text" x-model="search" placeholder="Cari wilayah..." x-ref="searchWilayah"
                            @click.stop x-init="$watch('open', v => v && $nextTick(() => $refs.searchWilayah.focus()))"
                            class="w-full text-sm border border-gray-200 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <ul class="max-h-52 overflow-y-auto py-1">
                        <template x-for="opt in filtered" :key="opt.value">
                            <li @click="pick(opt)" x-text="opt.label"
                                :class="opt.value == $wire.filterWilayahId ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-2 text-sm cursor-pointer"></li>
                        </template>
                        <li x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan</li>
                    </ul>
                </div>
            </div>

            {{-- Searchable Lingkungan --}}
            <div wire:key="lingkungan-dropdown-{{ $filterWilayahId }}" x-data="{
                    open: false,
                    search: '',
                    options: [
                        { value: '', label: 'Semua Lingkungan' },
                        @foreach ($this->lingkunganOptions as $l)
                        { value: '{{ $l->id }}', label: @js($l->nama) },
                        @endforeach
                    ],
                    get selected() { return this.options.find(o => o.value == $wire.filterLingkunganId) ?? this.options[0]; },
                    get filtered() {
                        if (!this.search) return this.options;
                        const q = this.search.toLowerCase();
                        return this.options.filter(o => o.label.toLowerCase().includes(q));
                    },
                    pick(opt) { $wire.set('filterLingkunganId', opt.value); this.open = false; this.search = ''; }
                }"
                @keydown.escape="open = false"
                @click.outside="open = false"
                class="relative">
                <button type="button" @click="open = !open"
                    class="w-52 flex items-center justify-between gap-2 text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <span class="truncate" x-text="selected.label"></span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="absolute z-50 mt-1 w-60 bg-white border border-gray-200 rounded-lg shadow-lg" style="display:none">
                    <div class="p-2 border-b border-gray-100">
                        <input type="text" x-model="search" placeholder="Cari lingkungan..." x-ref="searchLingkungan"
                            @click.stop x-init="$watch('open', v => v && $nextTick(() => $refs.searchLingkungan.focus()))"
                            class="w-full text-sm border border-gray-200 rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <ul class="max-h-52 overflow-y-auto py-1">
                        <template x-for="opt in filtered" :key="opt.value">
                            <li @click="pick(opt)" x-text="opt.label"
                                :class="opt.value == $wire.filterLingkunganId ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-2 text-sm cursor-pointer"></li>
                        </template>
                        <li x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan</li>
                    </ul>
                </div>
            </div>

            {{-- Loading indicator --}}
            <div wire:loading wire:target="search, filterWilayahId, filterLingkunganId, perPage, selectAllFiltered, clearSelection"
                class="flex items-center gap-1.5 text-xs text-primary-600 whitespace-nowrap shrink-0">
                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Memuat...
            </div>
        </div>
    </div>

    {{-- Toolbar seleksi cetak QR --}}
    @if (count($selectedIds) > 0)
        <div class="bg-primary-50 border border-primary-100 rounded-xl p-4 mb-5">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-primary-800">
                    {{ count($selectedIds) }} dari {{ $this->filteredCount }} keluarga terpilih
                </span>
                @if (count($selectedIds) < $this->filteredCount)
                    <button wire:click="selectAllFiltered" class="text-xs text-primary-600 hover:text-primary-800 underline font-medium">
                        Pilih semua {{ $this->filteredCount }}
                    </button>
                @endif
                <button wire:click="clearSelection" class="text-xs text-gray-500 hover:text-gray-700 underline">
                    Batalkan pilihan
                </button>
            </div>

            {{-- Pengaturan ukuran kertas & margin --}}
            <div class="mt-3 pt-3 border-t border-primary-100 flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Ukuran Kertas</label>
                    <select wire:model.live="printPaper"
                        class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="a4">A4 (210x297mm)</option>
                        <option value="f4">F4 / Folio (215x330mm)</option>
                        <option value="letter">Letter (215.9x279.4mm)</option>
                        <option value="custom">Custom...</option>
                    </select>
                </div>
                @if ($printPaper === 'custom')
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Lebar (mm)</label>
                        <input wire:model="printPaperWidth" type="number" min="50" max="500" step="0.1"
                            class="w-24 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tinggi (mm)</label>
                        <input wire:model="printPaperHeight" type="number" min="50" max="500" step="0.1"
                            class="w-24 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Margin Kertas (mm)</label>
                    <input wire:model="printMargin" type="number" min="0" max="50" step="0.5"
                        class="w-24 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jarak Antar Stiker (mm)</label>
                    <input wire:model="printGap" type="number" min="0" max="20" step="0.5"
                        class="w-24 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div x-data="{ qrSize: @entangle('printQrSize') }" class="flex-1 min-w-[180px]">
                    <label class="block text-xs text-gray-500 mb-1">
                        Ukuran QR dalam Stiker
                        <span class="font-semibold text-gray-700 ml-1" x-text="qrSize + '%'"></span>
                    </label>
                    <input type="range" min="20" max="90" step="5"
                        x-model="qrSize"
                        @change="$wire.set('printQrSize', parseInt(qrSize))"
                        class="w-full accent-primary-600">
                    <div class="flex justify-between text-xs text-gray-300 mt-0.5">
                        <span>20%</span>
                        <span>90%</span>
                    </div>
                </div>
            </div>

            {{-- Cetak satuan: posisi custom pada lembar stiker --}}
            <div class="mt-3 pt-3 border-t border-primary-100 flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Baris</label>
                    <input wire:model="printRows" type="number" min="1" max="20"
                        class="w-20 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kolom</label>
                    <input wire:model="printCols" type="number" min="1" max="10"
                        class="w-20 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Posisi Mulai</label>
                    <input wire:model="printStart" type="number" min="1"
                        class="w-24 text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <button wire:click="openPrint" type="button"
                    class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Massal
                </button>
                <p class="text-xs text-gray-400 max-w-xs">
                    Posisi 1 = pojok kiri atas. Gunakan untuk cetak di sisa lembar yang sudah terpakai.
                </p>
            </div>
        </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-opacity duration-150"
        wire:loading.class="opacity-50"
        wire:target="search, filterWilayahId, filterLingkunganId, perPage, selectAllFiltered, clearSelection">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 w-8">
                            <input type="checkbox"
                                x-data="{ total: {{ $this->filteredCount }} }"
                                x-bind:checked="$wire.selectedIds.length > 0 && $wire.selectedIds.length >= total"
                                x-effect="$el.indeterminate = $wire.selectedIds.length > 0 && $wire.selectedIds.length < total"
                                @click="$wire.selectedIds.length > 0 ? $wire.call('clearSelection') : $wire.call('selectAllFiltered')"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Kode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Kepala Keluarga</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">No. KK</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Lingkungan / Wilayah</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Ekonomi</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Anggota</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($this->families as $family)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $family->id }}"
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-primary-700 font-medium">{{ $family->kode_keluarga }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $family->nama_kepala_keluarga }}</td>
                            <td class="px-4 py-3 font-mono text-gray-500">{{ $family->no_kk_masked }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $family->lingkungan?->nama ?: '-' }}
                                <span class="text-gray-300">/</span>
                                {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $family->status_ekonomi === 'Pra Sejahtera' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $family->status_ekonomi }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $family->jml_anggota }} orang</td>
                            <td class="px-4 py-3">
                                <button wire:click="confirmDeactivate({{ $family->id }})"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors
                                        {{ $family->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $family->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $family->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('keluarga.qr', $family) }}" wire:navigate title="Lihat QR"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0h-2m0 0H8m12-4v.01M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m-4 14h2a2 2 0 002-2v-2M8 8h2v2H8V8zm6 0h2v2h-2V8zm-6 6h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('keluarga.riwayat', $family) }}" wire:navigate title="Riwayat Persembahan"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="openEdit({{ $family->id }})" title="Edit"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-400 text-sm">
                                Tidak ada data keluarga ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>Tampilkan</span>
                <select wire:model.live="perPage"
                    class="text-sm border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>per halaman &bull;
                    @if ($this->families->total() > 0)
                        {{ $this->families->firstItem() }}–{{ $this->families->lastItem() }} dari {{ $this->families->total() }} data
                    @else
                        0 data
                    @endif
                </span>
            </div>
            <div>
                {{ $this->families->links() }}
            </div>
        </div>
    </div>

    {{-- ============ MODAL FORM TAMBAH/EDIT ============ --}}
    <div x-data="{ show: @entangle('showFormModal') }" x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 overflow-y-auto"
        style="display:none">
        <div @click.outside="show = false" class="bg-white rounded-xl shadow-xl w-full max-w-lg my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    {{ $editingId ? 'Edit Data Keluarga' : 'Tambah Data Keluarga' }}
                </h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="save" class="px-6 py-5 space-y-4">
                @if ($editingId)
                    <div class="bg-gray-50 rounded-lg px-3 py-2 text-xs text-gray-500">
                        Kode Keluarga: <span class="font-mono font-semibold text-gray-700">{{ \App\Models\Family::find($editingId)?->kode_keluarga }}</span>
                    </div>
                @else
                    <p class="text-xs text-gray-400 bg-gray-50 rounded-lg px-3 py-2">
                        Kode keluarga dan QR token akan dibuat otomatis.
                    </p>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kepala Keluarga</label>
                    <input wire:model="nama_kepala_keluarga" type="text" placeholder="Nama kepala keluarga"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('nama_kepala_keluarga') border-red-300 @enderror">
                    @error('nama_kepala_keluarga') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Kartu Keluarga</label>
                    <input wire:model="no_kk" type="text" placeholder="16 digit No. KK"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('no_kk') border-red-300 @enderror">
                    @error('no_kk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Ekonomi</label>
                        <select wire:model="status_ekonomi"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="Sejahtera">Sejahtera</option>
                            <option value="Pra Sejahtera">Pra Sejahtera</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jml. Anggota</label>
                        <input wire:model="jml_anggota" type="number" min="1"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('jml_anggota') border-red-300 @enderror">
                        @error('jml_anggota') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Rumah</label>
                        <input wire:model="status_rumah" type="text" placeholder="Mis. Milik Sendiri, Sewa, dll"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp</label>
                        <input wire:model="no_hp" type="text" placeholder="Mis. 08123456789"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('no_hp') border-red-300 @enderror">
                        @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wilayah</label>
                        <select wire:model.live="wilayahId"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Pilih wilayah...</option>
                            @foreach ($this->wilayahOptions as $w)
                                <option value="{{ $w->id }}">{{ $w->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lingkungan</label>
                        <select wire:model="lingkunganId"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('lingkunganId') border-red-300 @enderror">
                            <option value="">Pilih lingkungan...</option>
                            @foreach ($this->formLingkunganOptions as $l)
                                <option value="{{ $l->id }}">{{ $l->nama }}</option>
                            @endforeach
                        </select>
                        @error('lingkunganId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="text-xs text-gray-400 -mt-2">
                    Wilayah/lingkungan baru bisa ditambahkan lewat menu "Wilayah & Lingkungan".
                </p>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('is_active')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none
                            {{ $is_active ? 'bg-primary-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                            {{ $is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm text-gray-700">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="show = false"
                        class="flex-1 text-sm border border-gray-200 text-gray-700 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-4 py-2 transition-colors">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Tambahkan' }}</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ MODAL KONFIRMASI AKTIF/NONAKTIF ============ --}}
    <div x-data="{ show: @entangle('showDeactivateConfirm') }" x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        style="display:none">
        <div @click.outside="show = false" class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Ubah Status Keluarga?</h3>
            <p class="text-sm text-gray-500 mb-6">Status aktif/nonaktif data keluarga ini akan diubah.</p>
            <div class="flex gap-3">
                <button @click="show = false"
                    class="flex-1 text-sm border border-gray-200 text-gray-700 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button wire:click="toggleActive"
                    class="flex-1 text-sm bg-amber-600 hover:bg-amber-700 text-white rounded-lg px-4 py-2 transition-colors">
                    <span wire:loading.remove wire:target="toggleActive">Ya, Ubah</span>
                    <span wire:loading wire:target="toggleActive">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ============ MODAL IMPOR EXCEL ============ --}}
    <div x-data="{ show: @entangle('showImportModal') }" x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
        style="display:none">
        <div @click.outside="show = false" class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Impor Data Keluarga</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <button wire:click="downloadTemplate"
                    class="w-full inline-flex items-center justify-center gap-2 text-sm border border-primary-200 text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg px-4 py-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Template Excel
                </button>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xlsx / .xls / .csv)</label>
                    <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-primary-50 file:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('importFile') border-red-300 @enderror">
                    @error('importFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="importFile" class="mt-1 text-xs text-gray-400">Mengunggah file...</div>
                </div>

                @if ($importResult)
                    <div class="rounded-lg bg-gray-50 border border-gray-100 px-4 py-3 text-sm space-y-1">
                        <p class="text-green-700">✓ {{ $importResult['imported'] }} data berhasil diimpor.</p>
                        @if ($importResult['skippedDuplicate'] > 0)
                            <p class="text-amber-700">⚠ {{ $importResult['skippedDuplicate'] }} data dilewati (No. KK duplikat).</p>
                        @endif
                        @if ($importResult['skippedInvalid'] > 0)
                            <p class="text-red-700">✗ {{ $importResult['skippedInvalid'] }} baris dilewati (data kosong/tidak valid).</p>
                        @endif
                    </div>
                @endif

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="show = false"
                        class="flex-1 text-sm border border-gray-200 text-gray-700 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">
                        Tutup
                    </button>
                    <button wire:click="import" wire:loading.attr="disabled" wire:target="import"
                        class="flex-1 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-4 py-2 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="import">Proses Impor</span>
                        <span wire:loading wire:target="import">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
