<div class="space-y-4">

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-1">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama / kode keluarga..."
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <select wire:model.live="wilayahId"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
                    <option value="">Semua Wilayah</option>
                    @foreach ($this->wilayahs as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="lingkunganId"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
                    <option value="">Semua Lingkungan</option>
                    @foreach ($this->lingkungans as $l)
                        <option value="{{ $l->id }}">{{ $l->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Daftar Keluarga</h3>
            <span class="text-xs text-gray-400">{{ $this->families->total() }} keluarga</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500">Kode</th>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500">Nama Kepala Keluarga</th>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500">Lingkungan / Wilayah</th>
                        <th class="px-4 py-2.5 text-right font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($this->families as $family)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3 font-mono text-xs text-primary-700 font-semibold whitespace-nowrap">
                                {{ $family->kode_keluarga }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $family->nama_kepala_keluarga }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $family->lingkungan?->nama ?: '-' }}
                                <span class="text-gray-300">/</span>
                                {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('laporan.keluarga.riwayat', $family) }}"
                                    wire:navigate
                                    class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-800">
                                    Lihat Riwayat
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                                Tidak ada keluarga yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->families->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $this->families->links() }}
            </div>
        @endif
    </div>

</div>
