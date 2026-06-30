<div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-5">
        <h2 class="text-base font-semibold text-gray-800">Hak Akses Role</h2>
        <p class="text-xs text-gray-500 mt-0.5">
            Atur menu apa saja yang boleh diakses oleh role Operator dan Bendahara.
            Admin selalu memiliki akses penuh ke seluruh fitur dan tidak bisa diubah di sini.
        </p>
    </div>

    {{-- Matrix --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">Menu</th>
                        @foreach (\App\Enums\Role::configurableRoles() as $role)
                            <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 py-3">
                                {{ $role->label() }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach (\App\Enums\Role::configurableMenus() as $menu => $menuLabel)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $menuLabel }}</td>
                            @foreach (\App\Enums\Role::configurableRoles() as $role)
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox"
                                        wire:model="matrix.{{ $menu }}.{{ $role->value }}"
                                        class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end px-4 py-3 border-t border-gray-100 bg-gray-50">
            <button wire:click="save"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>
