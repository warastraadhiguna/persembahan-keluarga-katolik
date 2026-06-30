<?php

namespace App\Livewire;

use App\Models\Lingkungan;
use App\Models\User;
use App\Services\PersembahanReportService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class YearlyReport extends Component
{
    public int $tahun;
    public string $wilayahId = '';
    public string $lingkunganId = '';
    public string $userId = '';

    public function mount(): void
    {
        $this->tahun = (int) now()->year;
    }

    public function updatedWilayahId(): void
    {
        $this->lingkunganId = '';
    }

    #[Computed]
    public function rows(): Collection
    {
        return app(PersembahanReportService::class)->yearly(
            $this->tahun,
            $this->wilayahId !== '' ? (int) $this->wilayahId : null,
            $this->lingkunganId !== '' ? (int) $this->lingkunganId : null,
            $this->userId !== '' ? (int) $this->userId : null,
        );
    }

    #[Computed]
    public function perBulanTotal(): array
    {
        $totals = array_fill(1, 12, 0.0);

        foreach ($this->rows as $row) {
            foreach ($row['per_bulan'] as $bulan => $nominal) {
                $totals[$bulan] += $nominal;
            }
        }

        return $totals;
    }

    #[Computed]
    public function grandTotal(): float
    {
        return (float) array_sum($this->perBulanTotal);
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
        return view('livewire.yearly-report');
    }
}
