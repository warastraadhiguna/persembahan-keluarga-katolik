<?php

namespace App\Livewire;

use App\Exports\FamilyTemplateExport;
use App\Imports\FamiliesImport;
use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\PrintSetting;
use App\Models\Wilayah;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ManageFamilies extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterWilayahId = '';
    public string $filterLingkunganId = '';

    // Form fields
    public ?int $editingId = null;
    public string $nama_kepala_keluarga = '';
    public string $no_kk = '';
    public string $status_ekonomi = 'Sejahtera';
    public int $jml_anggota = 1;
    public string $status_rumah = '';
    public string $wilayahId = '';
    public string $lingkunganId = '';
    public bool $is_active = true;

    // Modal states
    public bool $showFormModal = false;
    public bool $showDeactivateConfirm = false;
    public ?int $deactivatingId = null;
    public bool $showImportModal = false;

    // Import
    public $importFile = null;
    public ?array $importResult = null;

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

    private const PRINT_SETTING_FIELDS = [
        'printRows'         => 'rows',
        'printCols'         => 'cols',
        'printStart'        => 'start',
        'printPaper'        => 'paper',
        'printPaperWidth'   => 'paper_width',
        'printPaperHeight'  => 'paper_height',
        'printMargin'       => 'margin',
        'printGap'          => 'gap',
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

    protected function rules(): array
    {
        return [
            'nama_kepala_keluarga' => ['required', 'string', 'max:100'],
            'no_kk'                => ['required', 'string', 'max:16', Rule::unique('families', 'no_kk')->ignore($this->editingId)],
            'status_ekonomi'       => ['required', Rule::in(['Sejahtera', 'Pra Sejahtera'])],
            'jml_anggota'          => ['required', 'integer', 'min:1', 'max:50'],
            'status_rumah'         => ['nullable', 'string', 'max:50'],
            'lingkunganId'         => ['nullable', Rule::exists('lingkungans', 'id')],
        ];
    }

    protected array $messages = [
        'nama_kepala_keluarga.required' => 'Nama kepala keluarga wajib diisi.',
        'no_kk.required'                => 'No. KK wajib diisi.',
        'no_kk.unique'                  => 'No. KK sudah terdaftar.',
        'jml_anggota.required'          => 'Jumlah anggota wajib diisi.',
    ];

    public function updatingSearch(): void
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
            ->paginate(10);
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
        $this->reset(['editingId', 'nama_kepala_keluarga', 'no_kk', 'status_rumah', 'wilayahId', 'lingkunganId']);
        $this->status_ekonomi = 'Sejahtera';
        $this->jml_anggota    = 1;
        $this->is_active      = true;
        $this->showFormModal  = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        $family = Family::with('lingkungan')->findOrFail($id);
        $this->editingId             = $id;
        $this->nama_kepala_keluarga  = $family->nama_kepala_keluarga;
        $this->no_kk                 = $family->no_kk;
        $this->status_ekonomi        = $family->status_ekonomi;
        $this->jml_anggota           = $family->jml_anggota;
        $this->status_rumah          = (string) $family->status_rumah;
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
            'no_kk'                => $this->no_kk,
            'status_ekonomi'       => $this->status_ekonomi,
            'jml_anggota'          => $this->jml_anggota,
            'status_rumah'         => $this->status_rumah ?: null,
            'lingkungan_id'        => $this->lingkunganId ?: null,
            'is_active'            => $this->is_active,
        ];

        if ($this->editingId) {
            $family = Family::findOrFail($this->editingId);
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
        $this->importFile   = null;
        $this->importResult = null;
        $this->showImportModal = true;
        $this->resetErrorBag();
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
