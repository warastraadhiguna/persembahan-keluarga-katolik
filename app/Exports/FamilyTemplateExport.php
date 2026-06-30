<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamilyTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            '#',
            'Nama Kepala Keluarga',
            'No. Kartu Keluarga',
            'Status Ekonomi',
            'Jml Anggota Keluarga',
            'Status Rumah',
            'Lingkungan',
            'Wilayah',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
