<?php

namespace App\Livewire;

use App\Models\Lingkungan;
use App\Models\Wilayah;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WilayahLingkunganManager extends Component
{
    public string $newWilayahNama = '';

    public string $newLingkunganNama = '';
    public string $newLingkunganWilayahId = '';

    public ?int $editingWilayahId = null;
    public string $editingWilayahNama = '';

    public ?int $editingLingkunganId = null;
    public string $editingLingkunganNama = '';
    public string $editingLingkunganWilayahId = '';

    #[Computed]
    public function wilayahs()
    {
        return Wilayah::withCount('lingkungans')->orderBy('nama')->get();
    }

    #[Computed]
    public function lingkungans()
    {
        return Lingkungan::with('wilayah')->withCount('families')->orderBy('nama')->get();
    }

    public function addWilayah(): void
    {
        $this->validate([
            'newWilayahNama' => ['required', 'string', 'max:100', Rule::unique('wilayahs', 'nama')],
        ], [
            'newWilayahNama.required' => 'Nama wilayah wajib diisi.',
            'newWilayahNama.unique'   => 'Wilayah dengan nama ini sudah ada.',
        ]);

        Wilayah::create(['nama' => $this->newWilayahNama]);

        $this->newWilayahNama = '';
        unset($this->wilayahs);
        session()->flash('success', 'Wilayah berhasil ditambahkan.');
    }

    public function startEditWilayah(int $id): void
    {
        $wilayah = Wilayah::findOrFail($id);
        $this->editingWilayahId = $id;
        $this->editingWilayahNama = $wilayah->nama;
    }

    public function cancelEditWilayah(): void
    {
        $this->editingWilayahId = null;
        $this->editingWilayahNama = '';
    }

    public function saveWilayah(): void
    {
        $this->validate([
            'editingWilayahNama' => ['required', 'string', 'max:100', Rule::unique('wilayahs', 'nama')->ignore($this->editingWilayahId)],
        ], [
            'editingWilayahNama.required' => 'Nama wilayah wajib diisi.',
            'editingWilayahNama.unique'   => 'Wilayah dengan nama ini sudah ada.',
        ]);

        Wilayah::findOrFail($this->editingWilayahId)->update(['nama' => $this->editingWilayahNama]);

        $this->cancelEditWilayah();
        unset($this->wilayahs, $this->lingkungans);
        session()->flash('success', 'Wilayah berhasil diperbarui.');
    }

    public function deleteWilayah(int $id): void
    {
        $wilayah = Wilayah::findOrFail($id);

        if ($wilayah->lingkungans()->exists()) {
            session()->flash('error', 'Wilayah tidak bisa dihapus karena masih memiliki lingkungan.');

            return;
        }

        $wilayah->delete();
        unset($this->wilayahs);
        session()->flash('success', 'Wilayah berhasil dihapus.');
    }

    public function addLingkungan(): void
    {
        $this->validate([
            'newLingkunganWilayahId' => ['required', Rule::exists('wilayahs', 'id')],
            'newLingkunganNama'      => ['required', 'string', 'max:100', Rule::unique('lingkungans', 'nama')],
        ], [
            'newLingkunganWilayahId.required' => 'Pilih wilayah terlebih dahulu.',
            'newLingkunganNama.required'      => 'Nama lingkungan wajib diisi.',
            'newLingkunganNama.unique'        => 'Lingkungan dengan nama ini sudah ada.',
        ]);

        Lingkungan::create([
            'wilayah_id' => $this->newLingkunganWilayahId,
            'nama'       => $this->newLingkunganNama,
        ]);

        $this->newLingkunganNama = '';
        $this->newLingkunganWilayahId = '';
        unset($this->lingkungans, $this->wilayahs);
        session()->flash('success', 'Lingkungan berhasil ditambahkan.');
    }

    public function startEditLingkungan(int $id): void
    {
        $lingkungan = Lingkungan::findOrFail($id);
        $this->editingLingkunganId = $id;
        $this->editingLingkunganNama = $lingkungan->nama;
        $this->editingLingkunganWilayahId = (string) $lingkungan->wilayah_id;
    }

    public function cancelEditLingkungan(): void
    {
        $this->editingLingkunganId = null;
        $this->editingLingkunganNama = '';
        $this->editingLingkunganWilayahId = '';
    }

    public function saveLingkungan(): void
    {
        $this->validate([
            'editingLingkunganWilayahId' => ['required', Rule::exists('wilayahs', 'id')],
            'editingLingkunganNama'      => ['required', 'string', 'max:100', Rule::unique('lingkungans', 'nama')->ignore($this->editingLingkunganId)],
        ], [
            'editingLingkunganWilayahId.required' => 'Pilih wilayah terlebih dahulu.',
            'editingLingkunganNama.required'      => 'Nama lingkungan wajib diisi.',
            'editingLingkunganNama.unique'        => 'Lingkungan dengan nama ini sudah ada.',
        ]);

        Lingkungan::findOrFail($this->editingLingkunganId)->update([
            'wilayah_id' => $this->editingLingkunganWilayahId,
            'nama'       => $this->editingLingkunganNama,
        ]);

        $this->cancelEditLingkungan();
        unset($this->lingkungans, $this->wilayahs);
        session()->flash('success', 'Lingkungan berhasil diperbarui.');
    }

    public function deleteLingkungan(int $id): void
    {
        $lingkungan = Lingkungan::findOrFail($id);

        if ($lingkungan->families()->exists()) {
            session()->flash('error', 'Lingkungan tidak bisa dihapus karena masih dipakai oleh data keluarga.');

            return;
        }

        $lingkungan->delete();
        unset($this->lingkungans, $this->wilayahs);
        session()->flash('success', 'Lingkungan berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.wilayah-lingkungan-manager');
    }
}
