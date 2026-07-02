<?php

namespace App\Livewire;

use App\Models\Family;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecordTransaction extends Component
{
    public string $qrInput = '';
    public ?Family $family = null;

    public string $manualSearch = '';
    public bool $showScanModal = false;

    public int $bulan;
    public int $tahun;
    public int $tanggal;
    public string $nominal = '';
    public string $catatan = '';

    public array $recentTransactions = [];

    public function mount(): void
    {
        $this->bulan   = (int) now()->month;
        $this->tahun   = (int) now()->year;
        $this->tanggal = (int) now()->day;
        $this->recentTransactions = session()->get('persembahan_recent_transactions', []);
    }

    protected function rules(): array
    {
        return [
            'bulan'   => ['required', 'integer', 'between:1,12'],
            'tahun'   => ['required', 'integer', 'between:2000,2100'],
            'tanggal' => ['required', 'integer', 'between:1,31'],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected array $messages = [
        'nominal.required' => 'Nominal wajib diisi.',
        'nominal.numeric'  => 'Nominal harus berupa angka.',
        'nominal.min'      => 'Nominal harus lebih dari 0.',
    ];

    #[Computed]
    public function monthOptions(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    #[Computed]
    public function daysInMonth(): int
    {
        return \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->daysInMonth;
    }

    public function updatedBulan(): void
    {
        if ($this->tanggal > $this->daysInMonth) {
            $this->tanggal = $this->daysInMonth;
        }
    }

    public function updatedTahun(): void
    {
        if ($this->tanggal > $this->daysInMonth) {
            $this->tanggal = $this->daysInMonth;
        }
    }

    #[Computed]
    public function manualResults(): Collection
    {
        $term = trim($this->manualSearch);

        if ($term === '' || $this->family) {
            return collect();
        }

        return Family::query()
            ->with('lingkungan')
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->where('nama_kepala_keluarga', 'like', "%{$term}%")
                ->orWhere('kode_keluarga', 'like', "%{$term}%")
                ->orWhereHas('lingkungan', fn ($q2) => $q2->where('nama', 'like', "%{$term}%"))
            )
            ->orderBy('nama_kepala_keluarga')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function duplicateTransaction(): ?Transaction
    {
        if (! $this->family) {
            return null;
        }

        return Transaction::where('family_id', $this->family->id)
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->where('is_void', false)
            ->latest()
            ->first();
    }

    public function lookupByToken(): void
    {
        $token = trim($this->qrInput);
        $this->qrInput = '';

        if ($token === '') {
            return;
        }

        $family = Family::where('qr_token', $token)->first();

        if (! $family) {
            $this->addError('qrInput', 'QR tidak dikenali. Coba scan ulang atau cari manual di bawah.');

            return;
        }

        $this->selectFamily($family);
    }

    public function handleQrScanned(string $decodedText): void
    {
        $this->showScanModal = false;
        $this->qrInput = $decodedText;
        $this->lookupByToken();
    }

    public function selectFamilyManual(int $id): void
    {
        $family = Family::find($id);

        if ($family) {
            $this->selectFamily($family);
        }
    }

    public function clearFamily(): void
    {
        $this->family = null;
        $this->qrInput = '';
        $this->manualSearch = '';
        $this->nominal = '';
        $this->catatan = '';
        $this->resetErrorBag();
    }

    protected function selectFamily(Family $family): void
    {
        $family->loadMissing('lingkungan.wilayah');
        $this->family = $family;
        $this->manualSearch = '';
        $this->nominal = '';
        $this->catatan = '';
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! $this->family) {
            session()->flash('error', 'Pilih keluarga terlebih dahulu (scan QR atau cari manual).');

            return;
        }

        $this->validate();

        $transaction = Transaction::create([
            'family_id' => $this->family->id,
            'bulan'     => $this->bulan,
            'tahun'     => $this->tahun,
            'tanggal'   => $this->tanggal,
            'nominal'   => $this->nominal,
            'catatan'   => $this->catatan ?: null,
            'user_id'   => auth()->id(),
        ]);

        AuditLogger::log(
            'transaction.created',
            $transaction,
            "Mencatat persembahan {$this->family->nama_kepala_keluarga} (" . \App\Models\Transaction::monthLabel($this->bulan) . " {$this->tahun})",
            [],
            [
                'family_id' => $transaction->family_id,
                'bulan'     => $transaction->bulan,
                'tahun'     => $transaction->tahun,
                'nominal'   => (string) $transaction->nominal,
                'catatan'   => $transaction->catatan,
            ],
        );

        $entry = [
            'id'            => $transaction->id,
            'kode_keluarga' => $this->family->kode_keluarga,
            'nama'          => $this->family->nama_kepala_keluarga,
            'bulan'         => $this->bulan,
            'tahun'         => $this->tahun,
            'nominal'       => (float) $this->nominal,
            'catatan'       => $this->catatan,
            'waktu'         => now()->format('H:i'),
        ];

        array_unshift($this->recentTransactions, $entry);
        $this->recentTransactions = array_slice($this->recentTransactions, 0, 15);
        session()->put('persembahan_recent_transactions', $this->recentTransactions);

        session()->flash('success', "Persembahan {$this->family->nama_kepala_keluarga} berhasil disimpan.");

        $this->clearFamily();
    }

    public function render()
    {
        return view('livewire.record-transaction');
    }
}
