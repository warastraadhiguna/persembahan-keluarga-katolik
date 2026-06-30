<?php

namespace App\Livewire;

use App\Models\Lingkungan;
use App\Models\User;
use App\Services\PersembahanReportService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MonthlyReport extends Component
{
    public int $bulan;
    public int $tahun;
    public string $wilayahId = '';
    public string $lingkunganId = '';
    public string $userId = '';

    public function mount(): void
    {
        $this->bulan = (int) now()->month;
        $this->tahun = (int) now()->year;
    }

    public function updatedWilayahId(): void
    {
        $this->lingkunganId = '';
    }

    #[Computed]
    public function rows(): Collection
    {
        return app(PersembahanReportService::class)->monthly(
            $this->bulan,
            $this->tahun,
            $this->wilayahId !== '' ? (int) $this->wilayahId : null,
            $this->lingkunganId !== '' ? (int) $this->lingkunganId : null,
            $this->userId !== '' ? (int) $this->userId : null,
        );
    }

    #[Computed]
    public function totalNominal(): float
    {
        return (float) $this->rows->sum('nominal');
    }

    #[Computed]
    public function totalSudahBayar(): int
    {
        return $this->rows->where('sudah_bayar', true)->count();
    }

    #[Computed]
    public function totalBelumBayar(): int
    {
        return $this->rows->where('sudah_bayar', false)->count();
    }

    #[Computed]
    public function wilayahOptions(): Collection
    {
        return app(PersembahanReportService::class)->wilayahOptions();
    }

    #[Computed]
    public function lingkunganOptions(): Collection
    {
        return Lingkungan::query()
            ->when($this->wilayahId, fn ($q) => $q->where('wilayah_id', $this->wilayahId))
            ->orderBy('nama')
            ->get();
    }

    #[Computed]
    public function petugasOptions(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function resetFilters(): void
    {
        $this->wilayahId = '';
        $this->lingkunganId = '';
        $this->userId = '';
    }

    public function render()
    {
        return view('livewire.monthly-report');
    }
}
