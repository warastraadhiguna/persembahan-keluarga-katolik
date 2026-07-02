<div class="max-w-2xl">
    {{-- Success flash --}}
    @if ($saved)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.set('saved', false) }, 3000)"
            class="mb-5 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Data gereja berhasil disimpan.
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="mb-5 pb-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Identitas Gereja</h2>
            <p class="text-sm text-gray-500 mt-0.5">Data ini akan muncul sebagai kop pada setiap laporan cetak.</p>
        </div>

        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Gereja / Paroki <span class="text-red-500">*</span>
                </label>
                <input wire:model="nama" type="text" placeholder="mis. Paroki St. Paulus Miki Salatiga"
                    class="w-full text-sm border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $errors->has('nama') ? 'border-red-300' : 'border-gray-200' }}">
                @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
                <textarea wire:model="alamat" rows="3" placeholder="Jl. ... No. ..., Kelurahan, Kecamatan, Kota"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
                    <input wire:model="telepon" type="text" placeholder="0298-..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('telepon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input wire:model="email" type="email" placeholder="admin@gereja.org"
                        class="w-full text-sm border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $errors->has('email') ? 'border-red-300' : 'border-gray-200' }}">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Website <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <input wire:model="website" type="text" placeholder="https://..."
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('website') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors disabled:opacity-50">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <span wire:loading.remove wire:target="save">Simpan Data Gereja</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Preview kop --}}
    @if ($nama)
        <div class="mt-5 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Preview Kop Laporan</p>
            <div class="border-b-2 border-gray-700 pb-3">
                <p class="text-base font-bold text-gray-900 uppercase tracking-wide">{{ $nama }}</p>
                @if ($alamat)
                    <p class="text-xs text-gray-600 mt-1">{{ $alamat }}</p>
                @endif
                <div class="flex flex-wrap gap-x-5 mt-1 text-xs text-gray-600">
                    @if ($telepon) <span>Telp: {{ $telepon }}</span> @endif
                    @if ($email) <span>Email: {{ $email }}</span> @endif
                    @if ($website) <span>Web: {{ $website }}</span> @endif
                </div>
            </div>
        </div>
    @endif
</div>
