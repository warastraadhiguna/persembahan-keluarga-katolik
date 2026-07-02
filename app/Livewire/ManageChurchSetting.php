<?php

namespace App\Livewire;

use App\Models\ChurchSetting;
use Livewire\Component;

class ManageChurchSetting extends Component
{
    public string $nama     = '';
    public string $alamat   = '';
    public string $telepon  = '';
    public string $email    = '';
    public string $website  = '';

    public bool $saved = false;

    public function mount(): void
    {
        $setting = ChurchSetting::current();

        $this->nama    = $setting->nama    ?? '';
        $this->alamat  = $setting->alamat  ?? '';
        $this->telepon = $setting->telepon ?? '';
        $this->email   = $setting->email   ?? '';
        $this->website = $setting->website ?? '';
    }

    protected function rules(): array
    {
        return [
            'nama'    => ['required', 'string', 'max:200'],
            'alamat'  => ['nullable', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'string', 'max:200'],
        ];
    }

    protected array $messages = [
        'nama.required' => 'Nama gereja wajib diisi.',
        'email.email'   => 'Format email tidak valid.',
    ];

    public function save(): void
    {
        $this->validate();

        $setting = ChurchSetting::current();
        $setting->update([
            'nama'    => $this->nama,
            'alamat'  => $this->alamat  ?: null,
            'telepon' => $this->telepon ?: null,
            'email'   => $this->email   ?: null,
            'website' => $this->website ?: null,
        ]);

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.manage-church-setting');
    }
}
