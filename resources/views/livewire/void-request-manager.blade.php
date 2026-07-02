<div class="space-y-4">

    @if (session('success'))
        <div class="px-4 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="px-4 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Tab --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        <button wire:click="$set('tab', 'pending')"
            class="px-4 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $tab === 'pending' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Menunggu Persetujuan
            @if ($this->pendingRequests->count() > 0)
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-500 text-white text-xs font-bold">
                    {{ $this->pendingRequests->count() }}
                </span>
            @endif
        </button>
        <button wire:click="$set('tab', 'history')"
            class="px-4 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $tab === 'history' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Riwayat
        </button>
    </div>

    {{-- ===== TAB PENDING ===== --}}
    @if ($tab === 'pending')
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Keluarga</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Transaksi</th>
                            <th class="px-4 py-2.5 text-right font-medium text-gray-500">Nominal</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Alasan Pengajuan</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Diajukan Oleh</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Tanggal</th>
                            <th class="px-4 py-2.5 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($this->pendingRequests as $req)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $req->transaction?->family?->nama_kepala_keluarga }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $req->transaction?->family?->lingkungan?->nama }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ \App\Models\Transaction::monthLabel($req->transaction?->bulan) }} {{ $req->transaction?->tahun }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">
                                    @if ($req->transaction?->is_kosong)
                                        <span class="text-amber-600">Kosong</span>
                                    @else
                                        Rp {{ number_format((float) $req->transaction?->nominal, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs">
                                    <p class="line-clamp-2">{{ $req->reason }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $req->requester?->name }}</td>
                                <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="confirmApprove({{ $req->id }})"
                                            class="px-3 py-1 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">
                                            Setujui
                                        </button>
                                        <button wire:click="confirmReject({{ $req->id }})"
                                            class="px-3 py-1 text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 rounded-lg">
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                                    Tidak ada pengajuan yang menunggu persetujuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ===== TAB HISTORY ===== --}}
    @if ($tab === 'history')
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Keluarga</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Transaksi</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Alasan</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Diajukan Oleh</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Diproses Oleh</th>
                            <th class="px-4 py-2.5 text-left font-medium text-gray-500">Tanggal Proses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($this->historyRequests as $req)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $req->transaction?->family?->nama_kepala_keluarga }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $req->transaction?->family?->lingkungan?->nama }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                    {{ \App\Models\Transaction::monthLabel($req->transaction?->bulan) }} {{ $req->transaction?->tahun }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs">
                                    <p class="line-clamp-2">{{ $req->reason }}</p>
                                    @if ($req->review_note)
                                        <p class="text-xs text-red-400 mt-0.5 italic">Catatan: {{ $req->review_note }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $req->requester?->name }}</td>
                                <td class="px-4 py-3">
                                    @if ($req->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Disetujui</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $req->reviewer?->name }}</td>
                                <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $req->reviewed_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">
                                    Belum ada riwayat pengajuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Setujui --}}
    @if ($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelApprove">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-2">Setujui Pembatalan?</h3>
                <p class="text-sm text-gray-500 mb-5">
                    Transaksi akan dibatalkan dan tidak dihitung dalam rekap. Tindakan ini tidak bisa dibatalkan.
                </p>
                <div class="flex justify-end gap-2">
                    <button wire:click="cancelApprove"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-200">
                        Batal
                    </button>
                    <button wire:click="approve" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg disabled:opacity-50">
                        Ya, Setujui
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Tolak --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelReject">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Tolak Pengajuan</h3>
                <p class="text-sm text-gray-500 mb-4">Berikan alasan penolakan.</p>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="rejectNote" rows="3"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Contoh: Data sudah benar, tidak perlu dibatalkan"></textarea>
                @error('rejectNote') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-2 mt-5">
                    <button wire:click="cancelReject"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-200">
                        Batal
                    </button>
                    <button wire:click="reject" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg disabled:opacity-50">
                        Tolak Pengajuan
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
