<div>
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">Tombol cepat nominal yang tampil di form input persembahan.</p>
        <button wire:click="openCreate"
            class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 w-16">Urutan</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Label</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Nominal (Rp)</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600 w-24">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($this->presets as $preset)
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $preset->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3 text-gray-500 font-mono text-center">{{ $preset->urutan }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $preset->label }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">
                            Rp {{ number_format($preset->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $preset->id }})"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors
                                    {{ $preset->is_active
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $preset->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $preset->id }})"
                                    class="text-xs text-primary-600 hover:text-primary-800 font-medium">Edit</button>
                                <button wire:click="delete({{ $preset->id }})"
                                    wire:confirm="Hapus preset '{{ $preset->label }}'?"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada preset.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL --}}
    <div x-data="{ show: @entangle('showModal') }" x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        style="display:none">
        <div @click.outside="show = false"
            class="bg-white rounded-xl shadow-xl w-full max-w-sm"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">
                    {{ $editingId ? 'Edit Preset' : 'Tambah Preset' }}
                </h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input wire:model="urutan" type="number" min="0" max="255"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('urutan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <label class="flex items-center gap-2 mt-2.5 cursor-pointer">
                            <input wire:model="is_active" type="checkbox"
                                class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-600">Aktif</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-gray-400 font-normal">(tampil di tombol)</span></label>
                    <input wire:model="label" type="text" placeholder="Mis. 50.000"
                        class="w-full text-sm border {{ $errors->has('label') ? 'border-red-300' : 'border-gray-200' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                    <input wire:model="nominal" type="number" min="1" placeholder="50000"
                        class="w-full text-sm border {{ $errors->has('nominal') ? 'border-red-300' : 'border-gray-200' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('nominal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button @click="show = false"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button wire:click="save"
                    class="px-4 py-2 text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
