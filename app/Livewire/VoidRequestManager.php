<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\VoidRequest;
use App\Services\AuditLogger;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class VoidRequestManager extends Component
{
    public string $tab = 'pending'; // pending | history

    public ?int $approvingId = null;
    public ?int $rejectingId = null;
    public string $rejectNote = '';
    public bool $showApproveModal = false;
    public bool $showRejectModal = false;

    #[Computed]
    public function pendingRequests(): Collection
    {
        return VoidRequest::where('status', 'pending')
            ->with([
                'transaction.family.lingkungan',
                'requester:id,name',
            ])
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function historyRequests(): Collection
    {
        return VoidRequest::whereIn('status', ['approved', 'rejected'])
            ->with([
                'transaction.family.lingkungan',
                'requester:id,name',
                'reviewer:id,name',
            ])
            ->orderByDesc('reviewed_at')
            ->limit(100)
            ->get();
    }

    // ===== APPROVE =====

    public function confirmApprove(int $id): void
    {
        $this->approvingId     = $id;
        $this->showApproveModal = true;
        $this->resetErrorBag();
    }

    public function cancelApprove(): void
    {
        $this->showApproveModal = false;
        $this->approvingId     = null;
    }

    public function approve(): void
    {
        $voidRequest = VoidRequest::where('id', $this->approvingId)
            ->where('status', 'pending')
            ->with('transaction')
            ->first();

        if (! $voidRequest) {
            session()->flash('error', 'Pengajuan tidak ditemukan.');
            $this->cancelApprove();
            return;
        }

        $transaction = $voidRequest->transaction;

        $transaction->update([
            'is_void'     => true,
            'void_reason' => $voidRequest->reason,
            'voided_by'   => auth()->user()?->id,
            'voided_at'   => now(),
        ]);

        $voidRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->user()?->id,
            'reviewed_at' => now(),
        ]);

        AuditLogger::log(
            'void_request.approved',
            $transaction,
            "Menyetujui pembatalan transaksi {$transaction->family?->nama_kepala_keluarga} (" . Transaction::monthLabel($transaction->bulan) . " {$transaction->tahun})",
            ['is_void' => false],
            ['is_void' => true, 'void_reason' => $voidRequest->reason],
        );

        session()->flash('success', 'Pengajuan disetujui, transaksi berhasil dibatalkan.');
        $this->cancelApprove();
        unset($this->pendingRequests, $this->historyRequests);
    }

    // ===== REJECT =====

    public function confirmReject(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rejectNote      = '';
        $this->showRejectModal = true;
        $this->resetErrorBag();
    }

    public function cancelReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectingId     = null;
        $this->rejectNote      = '';
    }

    public function reject(): void
    {
        $this->validate([
            'rejectNote' => ['required', 'string', 'max:500'],
        ], [
            'rejectNote.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $voidRequest = VoidRequest::where('id', $this->rejectingId)
            ->where('status', 'pending')
            ->with('transaction')
            ->first();

        if (! $voidRequest) {
            session()->flash('error', 'Pengajuan tidak ditemukan.');
            $this->cancelReject();
            return;
        }

        $voidRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->user()?->id,
            'reviewed_at' => now(),
            'review_note' => $this->rejectNote,
        ]);

        AuditLogger::log(
            'void_request.rejected',
            $voidRequest->transaction,
            "Menolak pengajuan pembatalan transaksi {$voidRequest->transaction?->family?->nama_kepala_keluarga} (" . Transaction::monthLabel($voidRequest->transaction->bulan) . " {$voidRequest->transaction->tahun})",
            [],
            ['review_note' => $this->rejectNote],
        );

        session()->flash('success', 'Pengajuan ditolak.');
        $this->cancelReject();
        unset($this->pendingRequests, $this->historyRequests);
    }

    public function render()
    {
        return view('livewire.void-request-manager');
    }
}
