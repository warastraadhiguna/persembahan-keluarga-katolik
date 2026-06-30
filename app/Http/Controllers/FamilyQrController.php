<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\PrintSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FamilyQrController extends Controller
{
    public function show(Family $family)
    {
        $family->loadMissing('lingkungan.wilayah');

        $printSetting = PrintSetting::current();

        return view('keluarga-qr', compact('family', 'printSetting'));
    }

    public function history(Family $family)
    {
        $family->loadMissing('lingkungan.wilayah');

        return view('keluarga-riwayat', compact('family'));
    }

    private const PAPER_SIZES = [
        'a4'     => [210, 297],
        'f4'     => [215, 330],
        'letter' => [215.9, 279.4],
    ];

    public function cetak(Request $request)
    {
        $validated = $request->validate([
            'ids'          => ['required', 'string'],
            'rows'         => ['nullable', 'integer', 'min:1', 'max:20'],
            'cols'         => ['nullable', 'integer', 'min:1', 'max:10'],
            'start'        => ['nullable', 'integer', 'min:1'],
            'paper'        => ['nullable', Rule::in([...array_keys(self::PAPER_SIZES), 'custom'])],
            'paper_width'  => ['required_if:paper,custom', 'nullable', 'numeric', 'min:50', 'max:500'],
            'paper_height' => ['required_if:paper,custom', 'nullable', 'numeric', 'min:50', 'max:500'],
            'margin'       => ['nullable', 'numeric', 'min:0', 'max:50'],
            'gap'          => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);

        $ids = array_values(array_filter(array_map('intval', explode(',', $validated['ids']))));

        $families = Family::whereIn('id', $ids)->get()->sortBy(
            fn (Family $f) => array_search($f->id, $ids)
        )->values();

        $rows = (int) ($validated['rows'] ?? 8);
        $cols = (int) ($validated['cols'] ?? 3);
        $perPage = $rows * $cols;

        $start = max(1, min((int) ($validated['start'] ?? 1), $perPage));

        $pages = [];
        $slot = array_fill(0, $perPage, null);
        $cursor = $start - 1;

        foreach ($families as $family) {
            if ($cursor >= $perPage) {
                $pages[] = $slot;
                $slot = array_fill(0, $perPage, null);
                $cursor = 0;
            }
            $slot[$cursor] = $family;
            $cursor++;
        }
        $pages[] = $slot;

        $paper = $validated['paper'] ?? 'a4';
        [$paperWidth, $paperHeight] = $paper === 'custom'
            ? [(float) $validated['paper_width'], (float) $validated['paper_height']]
            : self::PAPER_SIZES[$paper];

        return view('keluarga-cetak', [
            'pages'       => $pages,
            'rows'        => $rows,
            'cols'        => $cols,
            'paperWidth'  => $paperWidth,
            'paperHeight' => $paperHeight,
            'margin'      => (float) ($validated['margin'] ?? 10),
            'gap'         => (float) ($validated['gap'] ?? 0),
        ]);
    }
}
