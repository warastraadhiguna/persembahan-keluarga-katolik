<?php

namespace App\Livewire;

use App\Models\PrintSetting;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ManagePrintSetting extends Component
{
    #[Rule(['required', 'integer', 'min:1', 'max:20'])]
    public int $rows = 8;

    #[Rule(['required', 'integer', 'min:1', 'max:10'])]
    public int $cols = 3;

    #[Rule(['required', 'in:a4,f4,letter,custom'])]
    public string $paper = 'a4';

    #[Rule(['nullable', 'numeric', 'min:50', 'max:500'])]
    public string $paperWidth = '210';

    #[Rule(['nullable', 'numeric', 'min:50', 'max:500'])]
    public string $paperHeight = '297';

    #[Rule(['required', 'numeric', 'min:0', 'max:50'])]
    public string $margin = '10';

    #[Rule(['required', 'numeric', 'min:0', 'max:20'])]
    public string $gap = '0';

    #[Rule(['required', 'integer', 'min:20', 'max:90'])]
    public int $qrSize = 55;

    public bool $saved = false;

    public function mount(): void
    {
        $s = PrintSetting::current();
        $this->rows        = $s->rows;
        $this->cols        = $s->cols;
        $this->paper       = $s->paper;
        $this->paperWidth  = (string) $s->paper_width;
        $this->paperHeight = (string) $s->paper_height;
        $this->margin      = (string) $s->margin;
        $this->gap         = (string) $s->gap;
        $this->qrSize      = $s->qr_size ?? 55;
    }

    public function save(): void
    {
        $this->validate();

        PrintSetting::updateSettings([
            'rows'         => $this->rows,
            'cols'         => $this->cols,
            'paper'        => $this->paper,
            'paper_width'  => $this->paperWidth,
            'paper_height' => $this->paperHeight,
            'margin'       => $this->margin,
            'gap'          => $this->gap,
            'qr_size'      => $this->qrSize,
        ]);

        $this->saved = true;
        $this->js("setTimeout(() => \$wire.set('saved', false), 3000)");
    }

    public function render()
    {
        return view('livewire.manage-print-setting');
    }
}
