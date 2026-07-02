<?php

namespace App\Livewire;

use App\Models\NominalPreset;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ManageNominalPresets extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $label   = '';
    public string $nominal = '';
    public int    $urutan  = 0;
    public bool   $is_active = true;

    protected function rules(): array
    {
        return [
            'label'   => ['required', 'string', 'max:20'],
            'nominal' => ['required', 'integer', 'min:1'],
            'urutan'  => ['required', 'integer', 'min:0', 'max:255'],
        ];
    }

    protected array $messages = [
        'label.required'   => 'Label wajib diisi.',
        'nominal.required' => 'Nominal wajib diisi.',
        'nominal.min'      => 'Nominal harus lebih dari 0.',
    ];

    #[Computed]
    public function presets()
    {
        return NominalPreset::query()->orderBy('urutan')->orderBy('nominal')->get();
    }

    public function openCreate(): void
    {
        $maxUrutan = NominalPreset::max('urutan') ?? 0;
        $this->reset(['editingId', 'label', 'nominal']);
        $this->urutan    = $maxUrutan + 1;
        $this->is_active = true;
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        $preset = NominalPreset::findOrFail($id);
        $this->editingId = $id;
        $this->label     = $preset->label;
        $this->nominal   = (string) $preset->nominal;
        $this->urutan    = $preset->urutan;
        $this->is_active = $preset->is_active;
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'label'     => $this->label,
            'nominal'   => (int) $this->nominal,
            'urutan'    => $this->urutan,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            NominalPreset::findOrFail($this->editingId)->update($data);
        } else {
            NominalPreset::create($data);
        }

        $this->showModal = false;
        unset($this->presets);
    }

    public function toggleActive(int $id): void
    {
        $preset = NominalPreset::findOrFail($id);
        $preset->update(['is_active' => ! $preset->is_active]);
        unset($this->presets);
    }

    public function delete(int $id): void
    {
        NominalPreset::findOrFail($id)->delete();
        unset($this->presets);
    }

    public function render()
    {
        return view('livewire.manage-nominal-presets');
    }
}
