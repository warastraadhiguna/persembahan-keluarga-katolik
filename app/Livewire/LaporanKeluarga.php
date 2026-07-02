<?php

namespace App\Livewire;

use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\Wilayah;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanKeluarga extends Component
{
    use WithPagination;

    public string $search = '';
    public string $wilayahId = '';
    public string $lingkunganId = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedWilayahId(): void
    {
        $this->lingkunganId = '';
        $this->resetPage();
    }

    public function updatedLingkunganId(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function wilayahs()
    {
        return Wilayah::orderBy('nama')->get();
    }

    #[Computed]
    public function lingkungans()
    {
        $q = Lingkungan::orderBy('nama');

        if ($this->wilayahId !== '') {
            $q->where('wilayah_id', $this->wilayahId);
        }

        return $q->get();
    }

    #[Computed]
    public function families(): LengthAwarePaginator
    {
        $q = Family::query()
            ->with('lingkungan.wilayah')
            ->where('is_active', true);

        if ($this->search !== '') {
            $term = $this->search;
            $q->where(fn ($sub) => $sub
                ->where('nama_kepala_keluarga', 'like', "%{$term}%")
                ->orWhere('kode_keluarga', 'like', "%{$term}%")
            );
        }

        if ($this->lingkunganId !== '') {
            $q->where('lingkungan_id', $this->lingkunganId);
        } elseif ($this->wilayahId !== '') {
            $q->whereHas('lingkungan', fn ($sub) => $sub->where('wilayah_id', $this->wilayahId));
        }

        return $q->orderBy('nama_kepala_keluarga')->paginate(20);
    }

    public function render()
    {
        return view('livewire.laporan-keluarga');
    }
}
