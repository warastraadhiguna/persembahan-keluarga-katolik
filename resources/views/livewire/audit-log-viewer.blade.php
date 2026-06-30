<div>
    {{-- Filter bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                <select wire:model.live="filterUserId" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua User</option>
                    @foreach ($this->userOptions as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Aksi</label>
                <select wire:model.live="filterAction" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Aksi</option>
                    @foreach (\App\Models\AuditLog::ACTIONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="filterDateFrom"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="filterDateTo"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="mt-4">
            <button wire:click="resetFilters" type="button" class="text-xs text-gray-500 hover:text-red-600">
                Reset Filter
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Waktu</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">User</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 whitespace-nowrap">Aksi</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Deskripsi</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 whitespace-nowrap">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($this->logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $log->user?->name ?: 'Sistem' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                                {{ $log->actionLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->description ?: '-' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($log->old_data || $log->new_data)
                                <button type="button" wire:click="toggleDetail({{ $log->id }})"
                                    class="text-xs font-medium text-primary-600 hover:text-primary-700">
                                    {{ $expandedId === $log->id ? 'Tutup' : 'Lihat' }}
                                </button>
                            @endif
                        </td>
                    </tr>
                    @if ($expandedId === $log->id)
                        <tr>
                            <td colspan="5" class="px-4 py-3 bg-gray-50">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                    <div>
                                        <p class="font-semibold text-gray-500 mb-1">Data Lama</p>
                                        @if ($log->old_data)
                                            <ul class="space-y-0.5 text-gray-600">
                                                @foreach ($log->old_data as $key => $value)
                                                    <li><span class="text-gray-400">{{ $key }}:</span> {{ is_bool($value) ? ($value ? 'true' : 'false') : ($value ?? '-') }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-400">-</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-500 mb-1">Data Baru</p>
                                        @if ($log->new_data)
                                            <ul class="space-y-0.5 text-gray-600">
                                                @foreach ($log->new_data as $key => $value)
                                                    <li><span class="text-gray-400">{{ $key }}:</span> {{ is_bool($value) ? ($value ? 'true' : 'false') : ($value ?? '-') }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-400">-</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada log audit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->logs->links() }}
    </div>
</div>
