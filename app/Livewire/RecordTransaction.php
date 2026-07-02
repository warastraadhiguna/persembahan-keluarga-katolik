<?php

namespace App\Livewire;

use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\NominalPreset;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class RecordTransaction extends Component
{
    use WithFileUploads;

    public string $qrInput = '';
    public ?Family $family = null;

    public string $manualSearch = '';
    public bool $showScanModal = false;

    public int $bulan;
    public int $tahun;
    public int $tanggal;
    public string $nominal = '';
    public string $catatan = '';
    public bool $isKosong = false;
    public mixed $buktiFoto = null;

    public array $recentTransactions = [];
    public ?string $waLink = null;
    public ?string $waName = null;

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
            'bulan'     => ['required', 'integer', 'between:1,12'],
            'tahun'     => ['required', 'integer', 'between:2000,2100'],
            'tanggal'   => ['required', 'integer', 'between:1,31'],
            'nominal'   => $this->isKosong ? ['nullable'] : ['required', 'numeric', 'min:0.01'],
            'catatan'   => ['nullable', 'string', 'max:1000'],
            'buktiFoto' => ['nullable', 'image', 'max:5120'],
        ];
    }

    protected array $messages = [
        'nominal.required'  => 'Nominal wajib diisi.',
        'nominal.numeric'   => 'Nominal harus berupa angka.',
        'nominal.min'       => 'Nominal harus lebih dari 0.',
        'buktiFoto.image'   => 'File harus berupa gambar.',
        'buktiFoto.max'     => 'Ukuran gambar maksimal 5 MB.',
    ];

    public function updatedIsKosong(): void
    {
        if ($this->isKosong) {
            $this->nominal = '';
            $this->resetErrorBag('nominal');
        }
    }

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
    public function nominalPresets()
    {
        return NominalPreset::active();
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
        $this->isKosong = false;
        $this->buktiFoto = null;
        $this->resetErrorBag();
    }

    protected function selectFamily(Family $family): void
    {
        $family->loadMissing('lingkungan.wilayah');
        $this->family = $family;
        $this->manualSearch = '';
        $this->nominal = '';
        $this->catatan = '';
        $this->isKosong = false;
        $this->buktiFoto = null;
        $this->waLink = null;
        $this->waName = null;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! $this->family) {
            session()->flash('error', 'Pilih keluarga terlebih dahulu (scan QR atau cari manual).');

            return;
        }

        $this->validate();

        $buktiPath = null;
        if ($this->buktiFoto) {
            $buktiPath = $this->compressAndStore($this->buktiFoto, $this->tahun, $this->bulan);
        }

        $transaction = Transaction::create([
            'family_id'  => $this->family->id,
            'bulan'      => $this->bulan,
            'tahun'      => $this->tahun,
            'tanggal'    => $this->tanggal,
            'nominal'    => $this->isKosong ? 0 : $this->nominal,
            'catatan'    => $this->catatan ?: null,
            'is_kosong'  => $this->isKosong,
            'bukti_foto' => $buktiPath,
            'user_id'    => auth()->id(),
        ]);

        AuditLogger::log(
            'transaction.created',
            $transaction,
            "Mencatat persembahan {$this->family->nama_kepala_keluarga} (" . Transaction::monthLabel($this->bulan) . " {$this->tahun})" . ($this->isKosong ? ' [KOSONG]' : ''),
            [],
            [
                'family_id'  => $transaction->family_id,
                'bulan'      => $transaction->bulan,
                'tahun'      => $transaction->tahun,
                'nominal'    => (string) $transaction->nominal,
                'is_kosong'  => $transaction->is_kosong,
                'catatan'    => $transaction->catatan,
            ],
        );

        $entry = [
            'id'            => $transaction->id,
            'kode_keluarga' => $this->family->kode_keluarga,
            'nama'          => $this->family->nama_kepala_keluarga,
            'bulan'         => $this->bulan,
            'tahun'         => $this->tahun,
            'nominal'       => $this->isKosong ? 0 : (float) $this->nominal,
            'is_kosong'     => $this->isKosong,
            'catatan'       => $this->catatan,
            'waktu'         => now()->format('H:i'),
        ];

        array_unshift($this->recentTransactions, $entry);
        $this->recentTransactions = array_slice($this->recentTransactions, 0, 15);
        session()->put('persembahan_recent_transactions', $this->recentTransactions);

        $label = $this->isKosong ? 'Amplop kosong' : 'Persembahan';
        session()->flash('success', "{$label} {$this->family->nama_kepala_keluarga} berhasil disimpan.");

        // Generate WA link jika keluarga punya no HP dan bukan kosong
        $this->waLink = null;
        $this->waName = null;
        if (! $this->isKosong && ! empty($this->family->no_hp)) {
            $phone      = $this->normalizePhone($this->family->no_hp);
            $bulanLabel = Transaction::monthLabel($this->bulan);
            $gereja     = ChurchSetting::current()->nama;
            $waktu      = now()->format('d-m-Y H:i:s');
            $pesan      = "Terima kasih sudah melakukan persembahan keluarga katolik bulan {$bulanLabel} {$this->tahun} tercatat pada {$waktu}." . ($gereja ? " {$gereja}." : '');
            $this->waLink = 'https://wa.me/' . $phone . '?text=' . rawurlencode($pesan);
            $this->waName = $this->family->nama_kepala_keluarga;
        }

        $this->clearFamily();
    }

    private function compressAndStore(mixed $file, int $tahun, int $bulan): string
    {
        $src  = $file->getRealPath();
        [$origW, $origH, $type] = getimagesize($src);

        $source = match ($type) {
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            IMAGETYPE_GIF  => imagecreatefromgif($src),
            default        => imagecreatefromjpeg($src),
        };

        // Auto-rotate JPEG based on EXIF orientation (phone photos)
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $orientation = $exif['Orientation'] ?? 1;
            $source = match ($orientation) {
                3 => imagerotate($source, 180, 0),
                6 => imagerotate($source, -90, 0),
                8 => imagerotate($source, 90, 0),
                default => $source,
            };
            if (in_array($orientation, [6, 8])) {
                [$origW, $origH] = [$origH, $origW];
            }
        }

        $maxSize = 1200;
        if ($origW > $maxSize || $origH > $maxSize) {
            $ratio   = min($maxSize / $origW, $maxSize / $origH);
            $newW    = (int) round($origW * $ratio);
            $newH    = (int) round($origH * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($source);
            $source = $resized;
        }

        $dir      = "bukti/{$tahun}/{$bulan}";
        $filename = Str::uuid().'.jpg';
        $path     = "{$dir}/{$filename}";

        Storage::disk('public')->makeDirectory($dir);
        imagejpeg($source, storage_path("app/public/{$path}"), 80);
        imagedestroy($source);

        return $path;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        $phone = ltrim($phone, '+');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    public function render()
    {
        return view('livewire.record-transaction');
    }
}
