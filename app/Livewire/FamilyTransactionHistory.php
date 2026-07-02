<?php

namespace App\Livewire;

use App\Models\Family;
use App\Models\Transaction;
use App\Models\VoidRequest;
use App\Services\AuditLogger;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class FamilyTransactionHistory extends Component
{
    public Family $family;
    public bool $readOnly = false;

    // Modal void langsung (untuk yang punya otoritas)
    public ?int $voidingId = null;
    public string $voidReason = '';
    public bool $showVoidModal = false;

    // Modal ajukan pembatalan (untuk yang tidak punya otoritas)
    public ?int $requestingId = null;
    public string $requestReason = '';
    public bool $showRequestModal = false;

    public function mount(Family $family, bool $readOnly = false): void
    {
        $this->family   = $family;
        $this->readOnly = $readOnly;
    }

    #[Computed]
    public function canApproveVoid(): bool
    {
        return auth()->user()?->canAccessMenu('persetujuan-void') ?? false;
    }

    #[Computed]
    public function transactions(): Collection
    {
        return $this->family->transactions()
            ->with(['petugas:id,name', 'voidedBy:id,name', 'pendingVoidRequest.requester:id,name'])
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

    // ===== VOID LANGSUNG (otoritas) =====

    public function confirmVoid(int $id): void
    {
        if ($this->readOnly || ! $this->canApproveVoid) {
            return;
        }

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
        if ($this->readOnly || ! $this->canApproveVoid) {
            return;
        }

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

        $transaction->update([
            'is_void'     => true,
            'void_reason' => $this->voidReason,
            'voided_by'   => auth()->user()?->id,
            'voided_at'   => now(),
        ]);

        // Tandai void request terkait sebagai approved jika ada
        VoidRequest::where('transaction_id', $transaction->id)
            ->where('status', 'pending')
            ->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->user()?->id,
                'reviewed_at' => now(),
                'review_note' => 'Disetujui sekaligus dibatalkan langsung.',
            ]);

        AuditLogger::log(
            'transaction.voided',
            $transaction,
            "Membatalkan transaksi {$this->family->nama_kepala_keluarga} (" . Transaction::monthLabel($transaction->bulan) . " {$transaction->tahun})",
            ['is_void' => false, 'void_reason' => null],
            ['is_void' => true, 'void_reason' => $this->voidReason],
        );

        session()->flash('success', 'Transaksi berhasil dibatalkan.');
        $this->cancelVoid();
        unset($this->transactions);
    }

    // ===== AJUKAN PEMBATALAN (tanpa otoritas) =====

    public function requestVoid(int $id): void
    {
        if ($this->readOnly || $this->canApproveVoid) {
            return;
        }

        $this->requestingId = $id;
        $this->requestReason = '';
        $this->showRequestModal = true;
        $this->resetErrorBag();
    }

    public function cancelRequest(): void
    {
        $this->showRequestModal = false;
        $this->requestingId = null;
        $this->requestReason = '';
    }

    public function submitVoidRequest(): void
    {
        if ($this->readOnly || $this->canApproveVoid) {
            return;
        }

        $this->validate([
            'requestReason' => ['required', 'string', 'max:500'],
        ], [
            'requestReason.required' => 'Alasan pengajuan wajib diisi.',
        ]);

        $transaction = Transaction::where('id', $this->requestingId)
            ->where('family_id', $this->family->id)
            ->where('is_void', false)
            ->first();

        if (! $transaction) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            $this->cancelRequest();
            return;
        }

        // Cegah duplikat request
        if ($transaction->pendingVoidRequest()->exists()) {
            session()->flash('error', 'Sudah ada pengajuan pembatalan yang sedang menunggu persetujuan.');
            $this->cancelRequest();
            return;
        }

        VoidRequest::create([
            'transaction_id' => $transaction->id,
            'requested_by'   => auth()->user()?->id,
            'reason'         => $this->requestReason,
            'status'         => 'pending',
        ]);

        AuditLogger::log(
            'void_request.created',
            $transaction,
            "Mengajukan permintaan pembatalan transaksi {$this->family->nama_kepala_keluarga} (" . Transaction::monthLabel($transaction->bulan) . " {$transaction->tahun})",
            [],
            ['reason' => $this->requestReason],
        );

        session()->flash('success', 'Pengajuan pembatalan berhasil dikirim, menunggu persetujuan.');
        $this->cancelRequest();
        unset($this->transactions);
    }

    public function render()
    {
        return view('livewire.family-transaction-history');
    }
}
