<div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <h2 class="text-base font-semibold text-gray-800">Master Data Wilayah & Lingkungan</h2>
        <p class="text-xs text-gray-500 mt-0.5">
            Kelola data wilayah dan lingkungan secara manual. Wilayah/lingkungan baru juga otomatis dibuat saat impor Excel data keluarga.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Kolom Wilayah --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Wilayah</h4>

            <form wire:submit="addWilayah" class="flex gap-2 mb-3">
                <input wire:model="newWilayahNama" type="text" placeholder="Nama wilayah baru"
                    class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('newWilayahNama') border-red-300 @enderror">
                <button type="submit"
                    class="text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-3 py-1.5 transition-colors">
                    Tambah
                </button>
            </form>
            @error('newWilayahNama') <p class="-mt-2 mb-3 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="border border-gray-100 rounded-lg divide-y divide-gray-50 max-h-80 overflow-y-auto">
                @forelse ($this->wilayahs as $w)
                    <div class="px-3 py-2 flex items-center gap-2">
                        @if ($editingWilayahId === $w->id)
                            <input wire:model="editingWilayahNama" type="text"
                                class="flex-1 text-sm border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <button wire:click="saveWilayah" class="text-xs text-primary-600 hover:text-primary-800 font-medium">Simpan</button>
                            <button wire:click="cancelEditWilayah" class="text-xs text-gray-400 hover:text-gray-600">Batal</button>
                        @else
                            <span class="flex-1 text-sm text-gray-700">{{ $w->nama }}</span>
                            <span class="text-xs text-gray-400">{{ $w->lingkungans_count }} lingkungan</span>
                            <button wire:click="startEditWilayah({{ $w->id }})" class="text-xs text-gray-400 hover:text-primary-600">Edit</button>
                            <button wire:click="deleteWilayah({{ $w->id }})" wire:confirm="Hapus wilayah ini?"
                                class="text-xs text-gray-400 hover:text-red-600">Hapus</button>
                        @endif
                    </div>
                @empty
                    <div class="px-3 py-6 text-center text-xs text-gray-400">Belum ada wilayah.</div>
                @endforelse
            </div>
            @error('editingWilayahNama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Kolom Lingkungan --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Lingkungan</h4>

            <form wire:submit="addLingkungan" class="space-y-2 mb-3">
                <select wire:model="newLingkunganWilayahId"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('newLingkunganWilayahId') border-red-300 @enderror">
                    <option value="">Pilih wilayah...</option>
                    @foreach ($this->wilayahs as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <input wire:model="newLingkunganNama" type="text" placeholder="Nama lingkungan baru"
                        class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('newLingkunganNama') border-red-300 @enderror">
                    <button type="submit"
                        class="text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-3 py-1.5 transition-colors">
                        Tambah
                    </button>
                </div>
            </form>
            @error('newLingkunganWilayahId') <p class="-mt-2 mb-3 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('newLingkunganNama') <p class="-mt-2 mb-3 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="border border-gray-100 rounded-lg divide-y divide-gray-50 max-h-80 overflow-y-auto">
                @forelse ($this->lingkungans as $l)
                    <div class="px-3 py-2">
                        @if ($editingLingkunganId === $l->id)
                            <div class="flex gap-2 mb-1">
                                <select wire:model="editingLingkunganWilayahId"
                                    class="flex-1 text-sm border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    @foreach ($this->wilayahs as $w)
                                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <input wire:model="editingLingkunganNama" type="text"
                                    class="flex-1 text-sm border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <button wire:click="saveLingkungan" class="text-xs text-primary-600 hover:text-primary-800 font-medium">Simpan</button>
                                <button wire:click="cancelEditLingkungan" class="text-xs text-gray-400 hover:text-gray-600">Batal</button>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="flex-1 text-sm text-gray-700">{{ $l->nama }}</span>
                                <span class="text-xs text-gray-400">{{ $l->wilayah?->nama }}</span>
                                <button wire:click="startEditLingkungan({{ $l->id }})" class="text-xs text-gray-400 hover:text-primary-600">Edit</button>
                                <button wire:click="deleteLingkungan({{ $l->id }})" wire:confirm="Hapus lingkungan ini?"
                                    class="text-xs text-gray-400 hover:text-red-600">Hapus</button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-3 py-6 text-center text-xs text-gray-400">Belum ada lingkungan.</div>
                @endforelse
            </div>
            @error('editingLingkunganNama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('editingLingkunganWilayahId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
    </div>
</div>
