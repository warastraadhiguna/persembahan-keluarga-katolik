<div>
    {{-- Filter bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                <select wire:model.live="bulan" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @foreach (\App\Models\Transaction::MONTHS as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                <input type="number" wire:model.live="tahun"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Wilayah</label>
                <select wire:model.live="wilayahId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Wilayah</option>
                    @foreach ($this->wilayahOptions as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Lingkungan</label>
                <select wire:model.live="lingkunganId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Lingkungan</option>
                    @foreach ($this->lingkunganOptions as $l)
                        <option value="{{ $l->id }}">{{ $l->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Petugas Pencatat</label>
                <select wire:model.live="userId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Petugas</option>
                    @foreach ($this->petugasOptions as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
            <button wire:click="resetFilters" type="button" class="text-xs text-gray-500 hover:text-red-600">
                Reset Filter
            </button>

            <div class="flex gap-2">
                <a href="{{ route('laporan.bulanan.excel', ['bulan' => $bulan, 'tahun' => $tahun, 'wilayah_id' => $wilayahId, 'lingkungan_id' => $lingkunganId, 'user_id' => $userId]) }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-green-700 border border-green-200 hover:bg-green-50 rounded-lg px-3 py-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor Excel
                </a>
                <a href="{{ route('laporan.bulanan.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'wilayah_id' => $wilayahId, 'lingkungan_id' => $lingkunganId, 'user_id' => $userId]) }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-red-700 border border-red-200 hover:bg-red-50 rounded-lg px-3 py-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Sudah Bayar</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $this->totalSudahBayar }} keluarga</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Belum Bayar</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $this->totalBelumBayar }} keluarga</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total Nominal</p>
            <p class="text-2xl font-bold text-primary-700 mt-1">Rp {{ number_format($this->totalNominal, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Kode</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Nama Kepala Keluarga</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Lingkungan / Wilayah</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 whitespace-nowrap">Nominal</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Petugas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($this->rows as $row)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $row['family']->kode_keluarga }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $row['family']->nama_kepala_keluarga }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $row['family']->lingkungan?->nama ?: '-' }} / {{ $row['family']->lingkungan?->wilayah?->nama ?: '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($row['sudah_bayar'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Sudah Bayar</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700 whitespace-nowrap">
                            {{ $row['nominal'] > 0 ? 'Rp ' . number_format($row['nominal'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $row['petugas'] ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data keluarga.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
