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
    p.sub { margin: 0 0 12px; color: #6b7280; font-size: 10px; text-align: center; }
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

    <h1>Rekap Persembahan Bulanan</h1>
    <p class="sub">
        {{ \App\Models\Transaction::monthLabel($bulan) }} {{ $tahun }}
        @if ($wilayah) &bull; Wilayah: {{ $wilayah }} @endif
        @if ($lingkungan) &bull; Lingkungan: {{ $lingkungan }} @endif
        @if ($petugasName) &bull; Petugas: {{ $petugasName }} @endif
    </p>

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
                    Total ({{ $rows->where('sudah_bayar', true)->count() }} sudah bayar,
                    {{ $rows->where('sudah_bayar', false)->count() }} belum bayar)
                </td>
                <td class="num">{{ number_format($rows->sum('nominal'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
