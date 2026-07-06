<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Stiker QR Keluarga</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 20px;
            background: #1e4d8b;
            color: #fff;
        }
        .toolbar button {
            cursor: pointer;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-print { background: #fff; color: #1e4d8b; }
        .btn-close { background: rgba(255,255,255,.15); color: #fff; }

        .sheet-wrapper {
            background: #e5e7eb;
            padding: 24px 0;
        }

        .sheet {
            width: {{ $paperWidth }}mm;
            height: {{ $paperHeight }}mm;
            margin: 0 auto 24px;
            padding: {{ $margin }}mm;
            background: #fff;
            box-shadow: 0 1px 6px rgba(0,0,0,.25);
            display: grid;
            grid-template-columns: repeat({{ $cols }}, 1fr);
            grid-template-rows: repeat({{ $rows }}, 1fr);
            gap: {{ $gap }}mm;
        }

        .cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px dashed #d1d5db;
            padding: 2mm;
            overflow: hidden;
        }
        .cell.empty { border-style: dotted; }
        .cell svg { width: {{ $qrSize }}%; height: auto; }
        .cell .kode { font-size: 8pt; font-weight: bold; color: #1e4d8b; margin-top: 1mm; }
        .cell .nama { font-size: 7pt; color: #374151; line-height: 1.15; margin-top: 0.5mm; }

        @media print {
            .toolbar { display: none; }
            .sheet-wrapper { background: #fff; padding: 0; }
            .sheet {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }
            .sheet:last-child { page-break-after: auto; }
        }

        @page {
            size: {{ $paperWidth }}mm {{ $paperHeight }}mm;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <span>{{ count($pages) }} lembar &bull; grid {{ $rows }}x{{ $cols }} &bull; kertas {{ rtrim(rtrim(number_format($paperWidth, 1), '0'), '.') }}x{{ rtrim(rtrim(number_format($paperHeight, 1), '0'), '.') }}mm &bull; QR {{ $qrSize }}%</span>
        <div style="display:flex; gap:8px;">
            <button class="btn-print" onclick="window.print()">Cetak Sekarang</button>
            <button class="btn-close" onclick="window.close()">Tutup Tab</button>
        </div>
    </div>

    <div class="sheet-wrapper">
        @foreach ($pages as $slots)
            <div class="sheet">
                @foreach ($slots as $family)
                    @if ($family)
                        <div class="cell">
                            {!! QrCode::size(150)->generate($family->qr_token) !!}
                            <div class="kode">{{ $family->kode_keluarga }}</div>
                            <div class="nama">{{ $family->nama_kepala_keluarga }}</div>
                        </div>
                    @else
                        <div class="cell empty"></div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

</body>
</html>
