<?php

namespace App\Livewire;

use App\Exports\FamilyTemplateExport;
use App\Imports\FamiliesImport;
use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\PrintSetting;
use App\Models\Wilayah;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ManageFamilies extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterWilayahId = '';
    public string $filterLingkunganId = '';
    public int $perPage = 25;

    // Form fields
    public ?int $editingId = null;
    public string $kode_keluarga = '';
    public string $nama_kepala_keluarga = '';
    public string $no_kk = '';
    public string $status_ekonomi = '';
    public ?int $jml_anggota = null;
    public string $status_rumah = '';
    public string $no_hp = '';
    public string $wilayahId = '';
    public string $lingkunganId = '';
    public bool $is_active = true;

    // Modal states
    public bool $showFormModal = false;
    public bool $showDeactivateConfirm = false;
    public ?int $deactivatingId = null;
    public bool $showImportModal = false;

    // Import Gereja (existing template format)
    public $importFile = null;
    public ?array $importResult = null;

    // Import Pengurus (WWLL sheet format)
    public string $importType = 'gereja';
    public $importPengurusFile = null;
    public int $importPengurusStep = 1;
    public array $parsedSheets = [];
    public array $selectedSheets = [];
    public bool $clearBeforeImport = false;
    public ?array $importPengurusResult = null;

    // Seleksi untuk cetak QR
    public array $selectedIds = [];
    public int $printRows = 8;
    public int $printCols = 3;
    public int $printStart = 1;
    public string $printPaper = 'a4';
    public string $printPaperWidth = '210';
    public string $printPaperHeight = '297';
    public string $printMargin = '10';
    public string $printGap = '0';
    public int $printQrSize = 55;

    private const PRINT_SETTING_FIELDS = [
        'printRows'         => 'rows',
        'printCols'         => 'cols',
        'printStart'        => 'start',
        'printPaper'        => 'paper',
        'printPaperWidth'   => 'paper_width',
        'printPaperHeight'  => 'paper_height',
        'printMargin'       => 'margin',
        'printGap'          => 'gap',
        'printQrSize'       => 'qr_size',
    ];

    public function mount(): void
    {
        $setting = PrintSetting::current();

        $this->printRows         = $setting->rows;
        $this->printCols         = $setting->cols;
        $this->printStart        = $setting->start;
        $this->printPaper        = $setting->paper;
        $this->printPaperWidth   = (string) $setting->paper_width;
        $this->printPaperHeight  = (string) $setting->paper_height;
        $this->printMargin       = (string) $setting->margin;
        $this->printGap          = (string) $setting->gap;
        $this->printQrSize       = $setting->qr_size ?? 55;
    }

    public function updated(string $name, $value): void
    {
        if (! array_key_exists($name, self::PRINT_SETTING_FIELDS)) {
            return;
        }

        PrintSetting::updateSettings([
            self::PRINT_SETTING_FIELDS[$name] => $value,
        ]);
    }

    public function openPrint(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        $url = route('keluarga.cetak', [
            'ids'          => implode(',', $this->selectedIds),
            'rows'         => $this->printRows,
            'cols'         => $this->printCols,
            'start'        => $this->printStart,
            'paper'        => $this->printPaper,
            'paper_width'  => $this->printPaperWidth,
            'paper_height' => $this->printPaperHeight,
            'margin'       => $this->printMargin,
            'gap'          => $this->printGap,
            'qr_size'      => $this->printQrSize,
        ]);

        $this->js("window.open(" . json_encode($url) . ", '_blank')");
    }

    protected function rules(): array
    {
        return [
            'kode_keluarga'        => ['nullable', 'string', 'max:20', Rule::unique('families', 'kode_keluarga')->ignore($this->editingId)],
            'nama_kepala_keluarga' => ['required', 'string', 'max:100'],
            'no_kk'                => ['nullable', 'string', 'max:16', Rule::unique('families', 'no_kk')->ignore($this->editingId)],
            'status_ekonomi'       => ['nullable', Rule::in(['Sejahtera', 'Pra Sejahtera'])],
            'jml_anggota'          => ['nullable', 'integer', 'min:1', 'max:50'],
            'status_rumah'         => ['nullable', 'string', 'max:50'],
            'no_hp'                => ['nullable', 'string', 'max:20'],
            'lingkunganId'         => ['nullable', Rule::exists('lingkungans', 'id')],
        ];
    }

    protected array $messages = [
        'nama_kepala_keluarga.required' => 'Nama kepala keluarga wajib diisi.',
        'no_kk.unique'                  => 'No. KK sudah terdaftar.',
        'kode_keluarga.unique'          => 'Kode ini sudah digunakan keluarga lain.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFilterWilayahId(): void
    {
        $this->filterLingkunganId = '';
        $this->resetPage();
    }

    public function updatingFilterLingkunganId(): void
    {
        $this->resetPage();
    }

    public function updatedWilayahId(): void
    {
        $this->lingkunganId = '';
    }

    protected function filteredQuery()
    {
        return Family::query()
            ->with('lingkungan.wilayah')
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('nama_kepala_keluarga', 'like', "%{$this->search}%")
                       ->orWhere('kode_keluarga', 'like', "%{$this->search}%")
                       ->orWhereHas('lingkungan', fn($q3) => $q3->where('nama', 'like', "%{$this->search}%"))
                )
            )
            ->when($this->filterWilayahId, fn($q) => $q->whereHas('lingkungan', fn($q2) => $q2->where('wilayah_id', $this->filterWilayahId)))
            ->when($this->filterLingkunganId, fn($q) => $q->where('lingkungan_id', $this->filterLingkunganId));
    }

    #[Computed]
    public function families()
    {
        return $this->filteredQuery()
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function filteredCount(): int
    {
        return $this->filteredQuery()->count();
    }

    #[Computed]
    public function wilayahOptions()
    {
        return Wilayah::query()->orderBy('nama')->get();
    }

    #[Computed]
    public function lingkunganOptions()
    {
        return Lingkungan::query()
            ->when($this->filterWilayahId, fn($q) => $q->where('wilayah_id', $this->filterWilayahId))
            ->orderBy('nama')
            ->get();
    }

    #[Computed]
    public function formLingkunganOptions()
    {
        return Lingkungan::query()
            ->when($this->wilayahId, fn($q) => $q->where('wilayah_id', $this->wilayahId))
            ->orderBy('nama')
            ->get();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'kode_keluarga', 'nama_kepala_keluarga', 'no_kk', 'status_ekonomi', 'status_rumah', 'no_hp', 'wilayahId', 'lingkunganId']);
        $this->jml_anggota   = null;
        $this->is_active     = true;
        $this->showFormModal = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        $family = Family::with('lingkungan')->findOrFail($id);
        $this->editingId             = $id;
        $this->kode_keluarga         = $family->kode_keluarga;
        $this->nama_kepala_keluarga  = $family->nama_kepala_keluarga;
        $this->no_kk                 = (string) ($family->no_kk ?? '');
        $this->status_ekonomi        = (string) ($family->status_ekonomi ?? '');
        $this->jml_anggota           = $family->jml_anggota;
        $this->status_rumah          = (string) ($family->status_rumah ?? '');
        $this->no_hp                 = (string) ($family->no_hp ?? '');
        $this->lingkunganId          = (string) ($family->lingkungan_id ?? '');
        $this->wilayahId             = (string) ($family->lingkungan?->wilayah_id ?? '');
        $this->is_active             = $family->is_active;
        $this->showFormModal         = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nama_kepala_keluarga' => $this->nama_kepala_keluarga,
            'no_kk'                => $this->no_kk ?: null,
            'status_ekonomi'       => $this->status_ekonomi ?: null,
            'jml_anggota'          => $this->jml_anggota ?: null,
            'status_rumah'         => $this->status_rumah ?: null,
            'no_hp'                => $this->no_hp ?: null,
            'lingkungan_id'        => $this->lingkunganId ?: null,
            'is_active'            => $this->is_active,
        ];

        if ($this->editingId) {
            $family = Family::findOrFail($this->editingId);
            // Update kode_keluarga only if user explicitly changed it
            if ($this->kode_keluarga && $this->kode_keluarga !== $family->kode_keluarga) {
                $data['kode_keluarga'] = $this->kode_keluarga;
            }
            $old = $family->only(array_keys($data));
            $family->update($data);

            AuditLogger::log(
                'family.updated',
                $family,
                "Memperbarui data keluarga {$family->nama_kepala_keluarga}",
                $old,
                $data,
            );

            session()->flash('success', 'Data keluarga berhasil diperbarui.');
        } else {
            if ($this->kode_keluarga) {
                $data['kode_keluarga'] = $this->kode_keluarga;
            }
            // If kode_keluarga is empty, booted() hook auto-generates based on lingkungan
            $family = Family::create($data);

            AuditLogger::log(
                'family.created',
                $family,
                "Menambahkan keluarga {$family->nama_kepala_keluarga}",
                [],
                $data,
            );

            session()->flash('success', 'Data keluarga berhasil ditambahkan.');
        }

        $this->showFormModal = false;
        unset($this->families, $this->wilayahOptions, $this->lingkunganOptions, $this->formLingkunganOptions);
    }

    public function confirmDeactivate(int $id): void
    {
        $this->deactivatingId = $id;
        $this->showDeactivateConfirm = true;
    }

    public function toggleActive(): void
    {
        $family = Family::findOrFail($this->deactivatingId);
        $wasActive = $family->is_active;
        $family->update(['is_active' => ! $wasActive]);

        $status = $family->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLogger::log(
            'family.updated',
            $family,
            "Keluarga {$family->nama_kepala_keluarga} {$status}",
            ['is_active' => $wasActive],
            ['is_active' => $family->is_active],
        );

        session()->flash('success', "Data keluarga berhasil {$status}.");

        $this->showDeactivateConfirm = false;
        unset($this->families);
    }

    public function selectAllOnPage(): void
    {
        $ids = $this->families->pluck('id')->all();
        $this->selectedIds = array_values(array_unique(array_merge($this->selectedIds, $ids)));
    }

    public function selectAllFiltered(): void
    {
        $ids = $this->filteredQuery()->pluck('id')->all();
        $this->selectedIds = array_values(array_unique(array_merge($this->selectedIds, $ids)));
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
    }

    public function openImport(): void
    {
        $this->importFile            = null;
        $this->importResult          = null;
        $this->importType            = 'gereja';
        $this->importPengurusFile    = null;
        $this->importPengurusStep    = 1;
        $this->parsedSheets          = [];
        $this->selectedSheets        = [];
        $this->clearBeforeImport     = false;
        $this->importPengurusResult  = null;
        $this->showImportModal       = true;
        $this->resetErrorBag();
    }

    public function parsePengurusFile(): void
    {
        $this->validate(['importPengurusFile' => ['required', 'file', 'mimes:xlsx,xls']]);

        $filePath    = $this->importPengurusFile->getRealPath();
        $reader      = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $sheets = [];
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (! preg_match('/^(\d{2})(\d{2})$/', $sheetName, $m)) {
                continue;
            }

            $wKode     = $m[1];
            $lKode     = $m[2];
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            $wilayahNama    = trim((string) $worksheet->getCell('D2')->getValue());
            $lingkunganNama = trim((string) $worksheet->getCell('C2')->getValue());
            $highestRow     = $worksheet->getHighestRow();

            $sheets[$sheetName] = [
                'code'            => $sheetName,
                'wilayah_kode'    => $wKode,
                'lingkungan_kode' => $lKode,
                'nama_wilayah'    => $wilayahNama ?: "Wilayah {$wKode}",
                'nama_lingkungan' => $lingkunganNama ?: "Lingkungan {$lKode}",
                'count'           => max(0, $highestRow - 1),
            ];
        }

        $this->parsedSheets   = $sheets;
        $this->selectedSheets = array_keys($sheets);
        $this->importPengurusStep = 2;
    }

    public function selectAllSheets(): void
    {
        $this->selectedSheets = array_keys($this->parsedSheets);
    }

    public function deselectAllSheets(): void
    {
        $this->selectedSheets = [];
    }

    public function importPengurus(): void
    {
        if (empty($this->selectedSheets)) {
            return;
        }

        $filePath    = $this->importPengurusFile->getRealPath();
        $reader      = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        if ($this->clearBeforeImport) {
            DB::table('transactions')->delete();
            DB::table('families')->delete();
        }

        $totalImported = 0;
        $skippedRows   = []; // ['sheet' => ..., 'row' => ..., 'kode' => ..., 'nama' => ..., 'reason' => ...]

        foreach ($this->selectedSheets as $sheetCode) {
            $info = $this->parsedSheets[$sheetCode] ?? null;
            if (! $info) {
                continue;
            }

            // Cari atau buat wilayah dengan kode yang sesuai
            $wilayah = Wilayah::firstOrCreate(
                ['kode' => $info['wilayah_kode']],
                ['nama' => $info['nama_wilayah']]
            );
            if (! str_contains($info['nama_wilayah'], 'Wilayah ')) {
                $wilayah->update(['nama' => $info['nama_wilayah']]);
            }

            // Cari atau buat lingkungan dengan kode dalam wilayah ini
            $lingkungan = Lingkungan::firstOrCreate(
                ['wilayah_id' => $wilayah->id, 'kode' => $info['lingkungan_kode']],
                ['nama' => $info['nama_lingkungan']]
            );
            if (! str_contains($info['nama_lingkungan'], 'Lingkungan ')) {
                $lingkungan->update(['nama' => $info['nama_lingkungan']]);
            }

            $worksheet  = $spreadsheet->getSheetByName($sheetCode);
            $highestRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rawKode = $worksheet->getCell("A{$row}")->getValue();
                $nama    = trim((string) $worksheet->getCell("B{$row}")->getValue());

                // Excel menyimpan "04.06.01" sebagai nilai waktu (0.17084...) karena mirip jam.
                // Kembalikan ke format WW.MM.SS jika nilai-nya float antara 0 dan 1.
                if (is_float($rawKode) && $rawKode > 0 && $rawKode < 1) {
                    $totalSeconds = (int) round($rawKode * 86400);
                    $h    = intdiv($totalSeconds, 3600);
                    $m    = intdiv($totalSeconds % 3600, 60);
                    $s    = $totalSeconds % 60;
                    $rawKode = sprintf('%02d.%02d.%02d', $h, $m, $s);
                }

                $kode = trim((string) $rawKode);

                if ($kode === '' || $nama === '') {
                    continue;
                }

                // Lewati sel berformat bukan kode
                if (! preg_match('/^\d{1,2}\.\d{2}\.\d+$/', $kode)) {
                    $skippedRows[] = [
                        'sheet'  => $sheetCode,
                        'row'    => $row,
                        'kode'   => $kode,
                        'nama'   => $nama,
                        'reason' => 'Format kode tidak valid',
                    ];
                    continue;
                }

                if (Family::where('kode_keluarga', $kode)->exists()) {
                    $skippedRows[] = [
                        'sheet'  => $sheetCode,
                        'row'    => $row,
                        'kode'   => $kode,
                        'nama'   => $nama,
                        'reason' => 'Kode sudah ada (duplikat)',
                    ];
                    continue;
                }

                Family::create([
                    'kode_keluarga'        => $kode,
                    'nama_kepala_keluarga' => $nama,
                    'lingkungan_id'        => $lingkungan->id,
                    'is_active'            => true,
                ]);
                $totalImported++;
            }
        }

        $sheetCount = count($this->selectedSheets);
        AuditLogger::log(
            'family.imported',
            null,
            "Import data pengurus: {$totalImported} keluarga dari {$sheetCount} sheet, " . count($skippedRows) . " baris dilewati"
        );

        $this->importPengurusResult = [
            'imported'     => $totalImported,
            'skipped'      => count($skippedRows),
            'skipped_rows' => $skippedRows,
            'sheets'       => $sheetCount,
        ];
        $this->importPengurusStep = 3;

        unset($this->families, $this->wilayahOptions, $this->lingkunganOptions, $this->formLingkunganOptions, $this->filteredCount);
    }

    public function downloadTemplate()
    {
        return Excel::download(new FamilyTemplateExport, 'template-data-keluarga.xlsx');
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'importFile.required' => 'Pilih file Excel terlebih dahulu.',
            'importFile.mimes'    => 'File harus berformat xlsx, xls, atau csv.',
        ]);

        $import = new FamiliesImport;

        Excel::import($import, $this->importFile->getRealPath());

        $this->importResult = [
            'imported'         => $import->imported,
            'skippedDuplicate' => $import->skippedDuplicate,
            'skippedInvalid'   => $import->skippedInvalid,
        ];

        $this->importFile = null;
        unset($this->families, $this->wilayahOptions, $this->lingkunganOptions, $this->formLingkunganOptions);
    }

    public function render()
    {
        return view('livewire.manage-families');
    }
}
