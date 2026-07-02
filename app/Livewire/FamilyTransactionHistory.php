<?php

namespace App\Livewire;

use App\Models\Family;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FamilyTransactionHistory extends Component
{
    public Family $family;

    public ?int $voidingId = null;
    public string $voidReason = '';
    public bool $showVoidModal = false;

    public function mount(Family $family): void
    {
        $this->family = $family;
    }

    #[Computed]
    public function transactions(): Collection
    {
        return $this->family->transactions()
            ->with(['petugas:id,name', 'voidedBy:id,name'])
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function activeTransactions(): Collection
    {
        return $this->transactions->where('is_void', false);
    }

    #[Computed]
    public function totalNominal(): float
    {
        return (float) $this->activeTransactions->sum('nominal');
    }

    #[Computed]
    public function monthsPaid(): int
    {
        return $this->activeTransactions
            ->unique(fn ($t) => $t->tahun . '-' . $t->bulan)
            ->count();
    }

    #[Computed]
    public function yearlyGrid(): array
    {
        $grid = [];
        foreach ($this->activeTransactions as $t) {
            $grid[$t->tahun][$t->bulan] = ($grid[$t->tahun][$t->bulan] ?? 0) + (float) $t->nominal;
        }
        krsort($grid);
        return $grid;
    }

    public function confirmVoid(int $id): void
    {
        $this->voidingId = $id;
        $this->voidReason = '';
        $this->showVoidModal = true;
        $this->resetErrorBag();
    }

    public function cancelVoid(): void
    {
        $this->showVoidModal = false;
        $this->voidingId = null;
        $this->voidReason = '';
    }

    public function void(): void
    {
        $this->validate([
            'voidReason' => ['required', 'string', 'max:500'],
        ], [
            'voidReason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $transaction = Transaction::where('id', $this->voidingId)
            ->where('family_id', $this->family->id)
            ->where('is_void', false)
            ->first();

        if (! $transaction) {
            session()->flash('error', 'Transaksi tidak ditemukan atau sudah dibatalkan.');
            $this->cancelVoid();

            return;
        }

        $old = [
            'is_void'     => false,
            'void_reason' => null,
        ];

        $transaction->update([
            'is_void'     => true,
            'void_reason' => $this->voidReason,
            'voided_by'   => auth()->id(),
            'voided_at'   => now(),
        ]);

        AuditLogger::log(
            'transaction.voided',
            $transaction,
            "Membatalkan transaksi {$this->family->nama_kepala_keluarga} (" . Transaction::monthLabel($transaction->bulan) . " {$transaction->tahun})",
            $old,
            [
                'is_void'     => true,
                'void_reason' => $this->voidReason,
            ],
        );

        session()->flash('success', 'Transaksi berhasil dibatalkan.');

        $this->cancelVoid();
        unset($this->transactions);
    }

    public function render()
    {
        return view('livewire.family-transaction-history');
    }
}
