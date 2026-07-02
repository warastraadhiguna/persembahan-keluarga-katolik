<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">

    {{-- Overlay untuk mobile --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-black/50 lg:hidden"
    ></div>

    {{-- SIDEBAR --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col bg-primary-600 text-white transition-transform duration-300 ease-in-out lg:translate-x-0"
    >
        {{-- Logo / Brand --}}
        <div class="flex items-center justify-between h-16 px-4 bg-primary-700 shrink-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-bold text-sm leading-tight">{{ config('app.name') }}</span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded text-white/70 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" wire:navigate>
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </x-slot>
                Dashboard
            </x-sidebar-link>

            {{-- Manajemen --}}
            <div class="pt-4 pb-1">
                <p class="px-3 text-xs font-semibold text-primary-200 uppercase tracking-wider">Manajemen</p>
            </div>

            @if (Auth::user()?->canAccessMenu('keluarga'))
                <x-sidebar-link href="{{ route('keluarga') }}" :active="request()->routeIs('keluarga*')" wire:navigate>
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </x-slot>
                    Umat / Keluarga
                </x-sidebar-link>
            @endif

            @if (Auth::user()?->canAccessMenu('persembahan'))
                <x-sidebar-link href="{{ route('persembahan') }}" :active="request()->routeIs('persembahan*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </x-slot>
                    Persembahan
                </x-sidebar-link>
            @endif

            @if (Auth::user()?->canAccessMenu('laporan'))
                <x-sidebar-link href="{{ route('laporan.bulanan') }}" wire:navigate :active="request()->routeIs('laporan.bulanan*') || request()->routeIs('laporan.tahunan*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </x-slot>
                    Laporan
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('laporan.keluarga') }}" wire:navigate :active="request()->routeIs('laporan.keluarga*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </x-slot>
                    Riwayat Keluarga
                </x-sidebar-link>
            @endif

            @if (Auth::user()?->canAccessMenu('audit'))
                <x-sidebar-link href="{{ route('audit.log') }}" wire:navigate :active="request()->routeIs('audit*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l3.586 3.586A1 1 0 0116 7.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </x-slot>
                    Log Audit
                </x-sidebar-link>
            @endif

            {{-- Master Data --}}
            @if (Auth::user()?->canAccessMenu('pengguna') || Auth::user()?->canAccessMenu('wilayah-lingkungan') || Auth::user()?->canAccessMenu('role-permission') || Auth::user()?->canAccessMenu('gereja') || Auth::user()?->canAccessMenu('nominal-presets'))
                <div class="pt-4 pb-1">
                    <p class="px-3 text-xs font-semibold text-primary-200 uppercase tracking-wider">Master Data</p>
                </div>

                @if (Auth::user()?->canAccessMenu('pengguna'))
                    <x-sidebar-link href="{{ route('pengguna') }}" :active="request()->routeIs('pengguna')" wire:navigate>
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </x-slot>
                        Pengguna
                    </x-sidebar-link>
                @endif

                @if (Auth::user()?->canAccessMenu('wilayah-lingkungan'))
                    <x-sidebar-link href="{{ route('wilayah-lingkungan') }}" wire:navigate :active="request()->routeIs('wilayah-lingkungan')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </x-slot>
                        Wilayah & Lingkungan
                    </x-sidebar-link>
                @endif

                @if (Auth::user()?->canAccessMenu('gereja'))
                    <x-sidebar-link href="{{ route('gereja') }}" wire:navigate :active="request()->routeIs('gereja')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </x-slot>
                        Data Gereja
                    </x-sidebar-link>
                @endif

                @if (Auth::user()?->canAccessMenu('nominal-presets'))
                    <x-sidebar-link href="{{ route('nominal-presets') }}" wire:navigate :active="request()->routeIs('nominal-presets')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </x-slot>
                        Preset Nominal
                    </x-sidebar-link>
                @endif

                @if (Auth::user()?->canAccessMenu('role-permission'))
                    <x-sidebar-link href="{{ route('role-permission') }}" wire:navigate :active="request()->routeIs('role-permission')">
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </x-slot>
                        Hak Akses Role
                    </x-sidebar-link>
                @endif
            @endif
        </nav>

        {{-- User info di bawah sidebar --}}
        <div class="shrink-0 border-t border-primary-500 p-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-primary-400 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? '' }}</p>
                    <p class="text-xs text-primary-200 truncate">
                        {{ Auth::user()?->role?->label() ?? '' }}
                    </p>
                </div>
            </div>
        </div>
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex flex-col min-h-screen lg:ml-64">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-10 flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200 shadow-sm shrink-0">
            {{-- Tombol hamburger (mobile) --}}
            <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Judul halaman --}}
            <div class="flex items-center gap-2">
                @isset($header)
                    {{ $header }}
                @endisset
            </div>

            {{-- Aksi kanan topbar --}}
            <div class="flex items-center gap-3">
                {{-- Dropdown profil --}}
                <div x-data="{ dropOpen: false }" class="relative">
                    <button @click="dropOpen = !dropOpen"
                        class="flex items-center gap-2 text-sm text-gray-700 hover:text-primary-600 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="hidden sm:block font-medium">{{ Auth::user()->name ?? '' }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div
                        x-show="dropOpen"
                        x-transition
                        @click.outside="dropOpen = false"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50"
                    >
                        <a href="{{ route('profile') }}" wire:navigate
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Profil Saya
                        </a>
                        <hr class="my-1 border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- KONTEN HALAMAN --}}
        <main class="flex-1 p-4 md:p-6">
            {{ $slot }}
        </main>

        {{-- FOOTER --}}
        <footer class="shrink-0 px-4 py-3 text-center text-xs text-gray-400 border-t border-gray-200 bg-white">
            &copy; {{ date('Y') }} Persembahan Katolik. Semua hak dilindungi.
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
