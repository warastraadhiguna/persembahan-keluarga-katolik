<div class="space-y-5">

    @if (session('success'))
        <div class="px-4 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="px-4 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- ===== SUMMARY STATS ===== --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-primary-600">{{ $this->monthsPaid }}</p>
            <p class="text-xs text-gray-500 mt-1">Bulan Lunas</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ count($this->yearlyGrid) }}</p>
            <p class="text-xs text-gray-500 mt-1">Tahun Aktif</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-gray-800">{{ $this->totalNominal > 0 ? 'Rp '.number_format($this->totalNominal, 0, ',', '.') : '-' }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Persembahan</p>
        </div>
    </div>

    {{-- ===== GRID TRACK RECORD ===== --}}
    @if (count($this->yearlyGrid) > 0)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Track Record Persembahan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Hijau = lunas, abu = belum ada catatan</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600 w-14">Tahun</th>
                            @foreach (\App\Models\Transaction::MONTHS as $num => $label)
                                <th class="px-1 py-2 text-center font-medium text-gray-500 min-w-[3rem]">
                                    {{ substr($label, 0, 3) }}
                                </th>
                            @endforeach
                            <th class="px-3 py-2 text-right font-semibold text-gray-600 whitespace-nowrap">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($this->yearlyGrid as $tahun => $months)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-3 py-2.5 font-bold text-gray-800">{{ $tahun }}</td>
                                @for ($m = 1; $m <= 12; $m++)
                                    <td class="px-1 py-2.5 text-center">
                                        @if (isset($months[$m]))
                                            <span class="w-7 h-7 rounded-full bg-green-100 inline-flex items-center justify-center" title="Rp {{ number_format($months[$m], 0, ',', '.') }}">
                                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="w-7 h-7 rounded-full bg-gray-100 inline-flex items-center justify-center text-gray-300 font-bold text-xs">-</span>
                                        @endif
                                    </td>
                                @endfor
                                <td class="px-3 py-2.5 text-right font-semibold text-gray-700 whitespace-nowrap">
                                    Rp {{ number_format(array_sum($months), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ===== TABEL DETAIL TRANSAKSI ===== --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Detail Transaksi</h3>
            <span class="text-xs text-gray-400">{{ $this->transactions->count() }} transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500 whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500 whitespace-nowrap">Bulan / Tahun</th>
                        <th class="px-4 py-2.5 text-right font-medium text-gray-500">Nominal</th>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500">Petugas</th>
                        <th class="px-4 py-2.5 text-left font-medium text-gray-500">Status</th>
                        <th class="px-4 py-2.5 text-center font-medium text-gray-500">Bukti</th>
                        @unless ($readOnly)
                            <th class="px-4 py-2.5 text-right font-medium text-gray-500">Aksi</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($this->transactions as $transaction)
                        @php $pending = $transaction->pendingVoidRequest; @endphp
                        <tr class="hover:bg-gray-50/50 {{ $transaction->is_void ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                {{ $transaction->tanggal ? str_pad($transaction->tanggal, 2, '0', STR_PAD_LEFT).'/'.substr(\App\Models\Transaction::monthLabel($transaction->bulan), 0, 3).'/'.$transaction->tahun : $transaction->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">
                                {{ \App\Models\Transaction::monthLabel($transaction->bulan) }} {{ $transaction->tahun }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap {{ $transaction->is_void ? 'line-through text-gray-400' : ($transaction->is_kosong ? 'text-amber-600' : 'text-gray-700') }}">
                                {{ $transaction->is_kosong ? 'Kosong' : 'Rp '.number_format((float) $transaction->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $transaction->petugas?->name ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($transaction->is_void)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Dibatalkan</span>
                                    @if ($transaction->void_reason)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $transaction->void_reason }}</p>
                                    @endif
                                @elseif ($pending)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Menunggu Persetujuan</span>
                                    <p class="text-xs text-gray-400 mt-0.5">oleh {{ $pending->requester?->name }}</p>
                                @elseif ($transaction->is_kosong)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Amplop Kosong</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Lunas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($transaction->bukti_foto)
                                    <a href="{{ $transaction->buktiFotoUrl() }}" target="_blank" rel="noopener">
                                        <img src="{{ $transaction->buktiFotoUrl() }}"
                                            class="h-10 w-10 object-cover rounded-lg border border-gray-200 mx-auto hover:scale-105 transition-transform"
                                            title="Lihat bukti foto">
                                    </a>
                                @else
                                    <span class="text-gray-300 text-xs">-</span>
                                @endif
                            </td>
                            @unless ($readOnly)
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @unless ($transaction->is_void)
                                        @if ($this->canApproveVoid)
                                            <button type="button" wire:click="confirmVoid({{ $transaction->id }})"
                                                class="text-xs font-medium text-red-500 hover:text-red-700">
                                                Batalkan
                                            </button>
                                        @elseif ($pending)
                                            <span class="text-xs text-orange-400 italic">Menunggu...</span>
                                        @else
                                            <button type="button" wire:click="requestVoid({{ $transaction->id }})"
                                                class="text-xs font-medium text-orange-500 hover:text-orange-700">
                                                Ajukan Pembatalan
                                            </button>
                                        @endif
                                    @endunless
                                </td>
                            @endunless
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $readOnly ? 6 : 7 }}" class="px-4 py-10 text-center text-gray-400 text-sm">
                                Belum ada transaksi untuk keluarga ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($this->activeTransactions->isNotEmpty())
                    <tfoot class="border-t border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-4 py-2.5 text-sm font-semibold text-gray-700">Total</td>
                            <td class="px-4 py-2.5 text-right text-sm font-bold text-gray-800 whitespace-nowrap">
                                Rp {{ number_format($this->totalNominal, 0, ',', '.') }}
                            </td>
                            <td colspan="{{ $readOnly ? 2 : 3 }}"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ===== MODAL: BATALKAN LANGSUNG (punya otoritas) ===== --}}
    @if ($showVoidModal && !$readOnly)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelVoid">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Batalkan Transaksi</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Transaksi akan ditandai dibatalkan dan tidak dihitung dalam rekap.
                </p>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Pembatalan <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="voidReason" rows="3"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Contoh: Salah input nominal"></textarea>
                @error('voidReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="cancelVoid"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-200">
                        Batal
                    </button>
                    <button type="button" wire:click="void" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg disabled:opacity-50">
                        Konfirmasi Batalkan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL: AJUKAN PEMBATALAN (tanpa otoritas) ===== --}}
    @if ($showRequestModal && !$readOnly)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelRequest">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Ajukan Pembatalan</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Pengajuan akan dikirim ke pemegang otoritas untuk disetujui atau ditolak.
                </p>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Pengajuan <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="requestReason" rows="3"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Jelaskan alasan pembatalan..."></textarea>
                @error('requestReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="cancelRequest"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-200">
                        Batal
                    </button>
                    <button type="button" wire:click="submitVoidRequest" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg disabled:opacity-50">
                        Kirim Pengajuan
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
