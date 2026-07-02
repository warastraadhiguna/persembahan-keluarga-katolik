<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
    .kop { border-bottom: 2.5px solid #1f2937; padding-bottom: 8px; margin-bottom: 10px; text-align: center; }
    .kop-nama { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 2px; }
    .kop-detail { font-size: 10px; color: #4b5563; margin: 0; }
    h1 { font-size: 13px; margin: 0 0 2px; font-weight: bold; text-align: center; }
    p.sub { margin: 0 0 10px; color: #6b7280; font-size: 10px; text-align: center; }
    .summary { display: table; width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .summary-cell { display: table-cell; width: 33.33%; text-align: center; border: 1px solid #d1d5db; padding: 7px 6px; background: #f9fafb; }
    .summary-cell .val { font-size: 15px; font-weight: bold; margin: 0 0 2px; }
    .summary-cell .lbl { font-size: 9px; color: #6b7280; margin: 0; }
    .val-ok  { color: #15803d; }
    .val-no  { color: #b91c1c; }
    .val-tot { color: #1d4ed8; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
    th { background: #f3f4f6; }
    td.num { text-align: right; }
    .badge-ok { color: #15803d; font-weight: bold; }
    .badge-no { color: #b91c1c; font-weight: bold; }
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

    <h1>Rekap Persembahan</h1>
    <p class="sub">
        @php
            $fmt = fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y');
        @endphp
        {{ $dateFrom === $dateTo ? $fmt($dateFrom) : $fmt($dateFrom).' s.d. '.$fmt($dateTo) }}
        @if ($statusFilter === 'sudah_bayar') &bull; Sudah Bayar @elseif ($statusFilter === 'belum_bayar') &bull; Belum Bayar @endif
        @if ($wilayah) &bull; Wilayah: {{ $wilayah }} @endif
        @if ($lingkungan) &bull; Lingkungan: {{ $lingkungan }} @endif
        @if ($petugasName) &bull; Petugas: {{ $petugasName }} @endif
    </p>

    @php
        $sudahBayar  = $rows->where('sudah_bayar', true)->count();
        $belumBayar  = $rows->where('sudah_bayar', false)->count();
        $totalNominal = $rows->sum('nominal');
    @endphp
    <div class="summary">
        <div class="summary-cell">
            <p class="val val-ok">{{ $sudahBayar }}</p>
            <p class="lbl">Amplop Sudah Bayar</p>
        </div>
        <div class="summary-cell">
            <p class="val val-no">{{ $belumBayar }}</p>
            <p class="lbl">Amplop Belum Bayar</p>
        </div>
        <div class="summary-cell">
            <p class="val val-tot" style="font-size:12px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
            <p class="lbl">Total Pendapatan</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Kode</th><th>Nama Kepala Keluarga</th><th>Lingkungan</th><th>Wilayah</th>
                <th>Status</th><th>Nominal</th><th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['family']->kode_keluarga }}</td>
                    <td>{{ $row['family']->nama_kepala_keluarga }}</td>
                    <td>{{ $row['family']->lingkungan?->nama ?: '-' }}</td>
                    <td>{{ $row['family']->lingkungan?->wilayah?->nama ?: '-' }}</td>
                    <td class="{{ $row['sudah_bayar'] ? 'badge-ok' : 'badge-no' }}">
                        {{ $row['sudah_bayar'] ? 'Sudah Bayar' : 'Belum Bayar' }}
                    </td>
                    <td class="num">{{ $row['nominal'] > 0 ? number_format($row['nominal'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $row['petugas'] ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    Total ({{ $sudahBayar }} sudah bayar, {{ $belumBayar }} belum bayar)
                </td>
                <td class="num">{{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td></td>
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
