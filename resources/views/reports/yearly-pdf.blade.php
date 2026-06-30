<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #1f2937; }
    h1 { font-size: 16px; margin: 0 0 2px; }
    p.sub { margin: 0 0 14px; color: #6b7280; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: right; }
    th { background: #f3f4f6; text-align: center; }
    td.name { text-align: left; }
    tfoot td { font-weight: bold; background: #f9fafb; }
</style>
</head>
<body>
    <h1>Rekap Persembahan Tahunan</h1>
    <p class="sub">
        Tahun {{ $tahun }}
        @if ($wilayah) &bull; Wilayah: {{ $wilayah }} @endif
        @if ($lingkungan) &bull; Lingkungan: {{ $lingkungan }} @endif
        @if ($petugasName) &bull; Petugas: {{ $petugasName }} @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th><th class="name">Nama Kepala Keluarga</th>
                @foreach (\App\Models\Transaction::MONTHS as $label)
                    <th>{{ substr($label, 0, 3) }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="name">{{ $row['family']->nama_kepala_keluarga }}</td>
                    @foreach ($row['per_bulan'] as $nominal)
                        <td>{{ $nominal > 0 ? number_format($nominal, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($row['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                @foreach ($perBulanTotal as $t)
                    <td>{{ number_format($t, 0, ',', '.') }}</td>
                @endforeach
                <td>{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
