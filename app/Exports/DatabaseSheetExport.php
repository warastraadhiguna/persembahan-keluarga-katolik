<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DatabaseSheetExport implements FromQuery, WithTitle, WithHeadings, WithMapping, WithColumnFormatting
{
    public function __construct(
        private readonly string $table,
        private readonly string $title,
        private readonly array  $columns,
    ) {}

    public function query()
    {
        return DB::table($this->table)->select($this->columns)->orderBy('id');
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return $this->columns;
    }

    public function map($row): array
    {
        return array_map(fn ($col) => $row->$col ?? null, $this->columns);
    }

    public function columnFormats(): array
    {
        // Format semua kolom sebagai teks supaya angka panjang (no_kk, id) tidak kehilangan digit
        $formats = [];
        foreach (range('A', 'Z') as $i => $letter) {
            if ($i >= count($this->columns)) break;
            $formats[$letter] = NumberFormat::FORMAT_TEXT;
        }
        return $formats;
    }
}
