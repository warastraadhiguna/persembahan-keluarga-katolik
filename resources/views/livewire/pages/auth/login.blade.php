<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="bg-gradient-to-br from-primary-700 to-primary-500 px-8 py-8 text-center">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">{{ config('app.name') }}</h1>
        <p class="text-sm text-white/70 mt-1">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    {{-- Form --}}
    <div class="bg-white px-8 py-7">
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-5">
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
                <x-text-input wire:model="form.email" id="email" class="block mt-1.5 w-full" type="email" name="email" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
                <x-text-input wire:model="form.password" id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember" class="inline-flex items-center gap-2 cursor-pointer">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500" name="remember">
                    <span class="text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <x-primary-button class="w-full justify-center py-2.5 text-sm">
                {{ __('Masuk') }}
            </x-primary-button>
        </form>
    </div>
</div>
