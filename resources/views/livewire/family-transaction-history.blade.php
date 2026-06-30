<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    @if (session('success'))
        <div class="m-4 mb-0 px-4 py-2.5 rounded-lg bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="m-4 mb-0 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Riwayat Transaksi Persembahan</h3>
        <span class="text-xs text-gray-400">{{ $this->transactions->count() }} transaksi</span>
    </div>

    <div class="max-h-[36rem] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
                <tr>
                    <th class="px-4 py-2.5 text-left font-medium text-gray-500">Bulan / Tahun</th>
                    <th class="px-4 py-2.5 text-right font-medium text-gray-500">Nominal</th>
                    <th class="px-4 py-2.5 text-left font-medium text-gray-500">Petugas</th>
                    <th class="px-4 py-2.5 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-2.5 text-left font-medium text-gray-500">Dicatat Pada</th>
                    <th class="px-4 py-2.5 text-right font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($this->transactions as $transaction)
                    <tr class="{{ $transaction->is_void ? 'opacity-60' : '' }}">
                        <td class="px-4 py-2.5 font-medium text-gray-800 whitespace-nowrap">
                            {{ \App\Models\Transaction::monthLabel($transaction->bulan) }} {{ $transaction->tahun }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-700 whitespace-nowrap {{ $transaction->is_void ? 'line-through' : '' }}">
                            Rp {{ number_format((float) $transaction->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ $transaction->petugas?->name ?: '-' }}</td>
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            @if ($transaction->is_void)
                                <span title="{{ $transaction->void_reason }}"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    Dibatalkan
                                </span>
                                <p class="text-xs text-gray-400 mt-1 max-w-[14rem]">{{ $transaction->void_reason }}</p>
                                <p class="text-xs text-gray-300">
                                    oleh {{ $transaction->voidedBy?->name ?: '-' }}
                                    @if ($transaction->voided_at)
                                        &middot; {{ $transaction->voided_at->format('d/m/Y H:i') }}
                                    @endif
                                </p>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Lunas
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-gray-400 whitespace-nowrap">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            @unless ($transaction->is_void)
                                <button type="button" wire:click="confirmVoid({{ $transaction->id }})"
                                    class="text-xs font-medium text-red-600 hover:text-red-700">
                                    Batalkan
                                </button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi untuk keluarga ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal konfirmasi pembatalan transaksi --}}
    @if ($showVoidModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelVoid">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Batalkan Transaksi</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Transaksi tidak akan dihapus, hanya ditandai dibatalkan dan tidak dihitung dalam rekap.
                    Jika nominal salah, buat transaksi baru setelah ini dibatalkan.
                </p>

                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pembatalan <span class="text-red-500">*</span></label>
                <textarea wire:model="voidReason" rows="3"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Contoh: Salah input nominal, seharusnya Rp50.000"></textarea>
                @error('voidReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" wire:click="cancelVoid"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg">
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
</div>
