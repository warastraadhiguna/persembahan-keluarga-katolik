<div>
    {{-- Filter bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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
                <a href="{{ route('laporan.tahunan.excel', ['tahun' => $tahun, 'wilayah_id' => $wilayahId, 'lingkungan_id' => $lingkunganId, 'user_id' => $userId]) }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-green-700 border border-green-200 hover:bg-green-50 rounded-lg px-3 py-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor Excel
                </a>
                <a href="{{ route('laporan.tahunan.pdf', ['tahun' => $tahun, 'wilayah_id' => $wilayahId, 'lingkungan_id' => $lingkunganId, 'user_id' => $userId]) }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-red-700 border border-red-200 hover:bg-red-50 rounded-lg px-3 py-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Matriks tahunan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 overflow-x-auto">
        <table class="text-sm border-collapse w-full">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left font-medium text-gray-500 sticky left-0 bg-gray-50 whitespace-nowrap">Nama Kepala Keluarga</th>
                    @foreach (\App\Models\Transaction::MONTHS as $label)
                        <th class="px-2 py-2 text-right font-medium text-gray-500 whitespace-nowrap">{{ substr($label, 0, 3) }}</th>
                    @endforeach
                    <th class="px-3 py-2 text-right font-medium text-gray-700 whitespace-nowrap">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($this->rows as $row)
                    <tr>
                        <td class="px-3 py-2 font-medium text-gray-800 sticky left-0 bg-white whitespace-nowrap">{{ $row['family']->nama_kepala_keluarga }}</td>
                        @foreach ($row['per_bulan'] as $nominal)
                            <td class="px-2 py-2 text-right text-gray-600 whitespace-nowrap">
                                {{ $nominal > 0 ? number_format($nominal, 0, ',', '.') : '-' }}
                            </td>
                        @endforeach
                        <td class="px-3 py-2 text-right font-semibold text-primary-700 whitespace-nowrap">
                            {{ number_format($row['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="14" class="px-4 py-8 text-center text-gray-400">Tidak ada data keluarga.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td class="px-3 py-2 text-gray-700 sticky left-0 bg-gray-50 whitespace-nowrap">Total</td>
                    @foreach ($this->perBulanTotal as $t)
                        <td class="px-2 py-2 text-right text-gray-700 whitespace-nowrap">{{ number_format($t, 0, ',', '.') }}</td>
                    @endforeach
                    <td class="px-3 py-2 text-right text-primary-700 whitespace-nowrap">{{ number_format($this->grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
