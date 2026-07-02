<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #1f2937; }
    .kop { border-bottom: 2.5px solid #1f2937; padding-bottom: 7px; margin-bottom: 9px; text-align: center; }
    .kop-nama { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 2px; }
    .kop-detail { font-size: 9px; color: #4b5563; margin: 0; }
    h1 { font-size: 12px; margin: 0 0 2px; font-weight: bold; text-align: center; }
    p.sub { margin: 0 0 10px; color: #6b7280; text-align: center; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: right; }
    th { background: #f3f4f6; text-align: center; }
    td.name { text-align: left; }
    tfoot td { font-weight: bold; background: #f9fafb; }
</style>
</head>
<body>
    @php $church = \App\Models\ChurchSetting::current(); @endphp
    @if ($church->nama)
        <div class="kop">
            <p class="kop-nama">{{ $church->nama }}</p>
            @if ($church->alamat)
                <p class="kop-detail">{{ $church->alamat }}</p>
            @endif
            @if ($church->telepon || $church->email)
                <p class="kop-detail">
                    @if ($church->telepon) Telp: {{ $church->telepon }} @endif
                    @if ($church->telepon && $church->email) &nbsp;&bull;&nbsp; @endif
                    @if ($church->email) Email: {{ $church->email }} @endif
                </p>
            @endif
        </div>
    @endif

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

    <div style="margin-top:40px; text-align:right; font-size:11px; font-family:Helvetica,Arial,sans-serif;">
        <p style="margin:0 0 4px;">________________, ______________________</p>
        <p style="margin:0 0 50px; text-align:center; width:220px; display:inline-block;">Mengetahui,</p>
        <br>
        <p style="margin:0; text-align:center; width:220px; display:inline-block;">________________________________________</p>
    </div>
</body>
</html>
