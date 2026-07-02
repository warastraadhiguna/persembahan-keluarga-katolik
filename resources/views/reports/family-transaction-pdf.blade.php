<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 16px; }

    /* KOP */
    .kop { border-bottom: 2px solid #1f2937; padding-bottom: 6px; margin-bottom: 12px; text-align: center; }
    .kop-nama { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 2px; }
    .kop-detail { font-size: 9px; color: #4b5563; margin: 0; }

    /* Judul */
    .judul { text-align: center; margin-bottom: 12px; }
    .judul h1 { font-size: 13px; font-weight: bold; margin: 0 0 2px; }
    .judul .nama { font-size: 12px; font-weight: bold; color: #1d4ed8; margin: 0 0 2px; }
    .judul .info { font-size: 9px; color: #6b7280; margin: 0; }

    /* Stats */
    .stats { display: table; width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .stat-cell { display: table-cell; width: 33%; text-align: center; border: 1px solid #e5e7eb;
                 padding: 6px 4px; background: #f9fafb; }
    .stat-cell .val { font-size: 16px; font-weight: bold; color: #1d4ed8; }
    .stat-cell .lbl { font-size: 8px; color: #6b7280; margin-top: 1px; }

    /* Section header */
    .section-header { background: #1d4ed8; color: #fff; font-weight: bold; font-size: 10px;
                      padding: 4px 8px; margin-bottom: 0; }

    /* Track record table */
    .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 8.5px; }
    .grid-table th { background: #dbeafe; text-align: center; padding: 3px 2px; border: 1px solid #d1d5db; font-weight: bold; }
    .grid-table td { border: 1px solid #d1d5db; padding: 3px 2px; text-align: center; }
    .grid-table td.tahun { font-weight: bold; text-align: left; padding-left: 5px; }
    .grid-table td.paid { background: #dcfce7; color: #15803d; }
    .grid-table td.empty { color: #d1d5db; }
    .grid-table td.total { font-weight: bold; text-align: right; padding-right: 4px; }

    /* Detail table */
    .detail-table { width: 100%; border-collapse: collapse; font-size: 9px; }
    .detail-table th { background: #dbeafe; padding: 4px 6px; border: 1px solid #d1d5db; text-align: left; font-weight: bold; }
    .detail-table td { border: 1px solid #d1d5db; padding: 3px 6px; }
    .detail-table td.num { text-align: right; }
    .detail-table td.center { text-align: center; }
    .detail-table .void-row td { color: #9ca3af; }
    .detail-table .total-row td { font-weight: bold; background: #f3f4f6; }
    .badge-ok { color: #15803d; font-weight: bold; }
    .badge-void { color: #9ca3af; }
    .print-date { text-align: right; font-size: 8px; color: #9ca3af; margin-top: 16px; }
</style>
</head>
<body>

    {{-- KOP --}}
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

    {{-- JUDUL --}}
    <div class="judul">
        <h1>Laporan Persembahan Keluarga</h1>
        <p class="nama">{{ $family->nama_kepala_keluarga }}</p>
        <p class="info">
            Kode: {{ $family->kode_keluarga }}
            &bull; Lingkungan: {{ $family->lingkungan?->nama ?: '-' }}
            &bull; Wilayah: {{ $family->lingkungan?->wilayah?->nama ?: '-' }}
        </p>
    </div>

    {{-- STATS --}}
    <div class="stats">
        <div class="stat-cell">
            <div class="val">{{ $monthsPaid }}</div>
            <div class="lbl">Bulan Lunas</div>
        </div>
        <div class="stat-cell">
            <div class="val">{{ count($yearlyGrid) }}</div>
            <div class="lbl">Tahun Aktif</div>
        </div>
        <div class="stat-cell">
            <div class="val" style="font-size:12px;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            <div class="lbl">Total Persembahan</div>
        </div>
    </div>

    {{-- TRACK RECORD GRID --}}
    @if (count($yearlyGrid) > 0)
        <div class="section-header">Track Record Persembahan</div>
        <table class="grid-table">
            <thead>
                <tr>
                    <th style="text-align:left;padding-left:5px;width:40px;">Tahun</th>
                    @foreach (\App\Models\Transaction::MONTHS as $label)
                        <th>{{ substr($label, 0, 3) }}</th>
                    @endforeach
                    <th style="text-align:right;padding-right:4px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($yearlyGrid as $tahun => $months)
                    <tr>
                        <td class="tahun">{{ $tahun }}</td>
                        @for ($m = 1; $m <= 12; $m++)
                            @if (isset($months[$m]))
                                <td class="paid" title="Rp {{ number_format($months[$m], 0, ',', '.') }}">&#10003;</td>
                            @else
                                <td class="empty">-</td>
                            @endif
                        @endfor
                        <td class="total">Rp {{ number_format(array_sum($months), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- DETAIL TRANSAKSI --}}
    <div class="section-header">Detail Transaksi</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:24px;">#</th>
                <th style="width:62px;">Tanggal</th>
                <th style="width:80px;">Bulan / Tahun</th>
                <th style="text-align:right;width:80px;">Nominal</th>
                <th>Petugas</th>
                <th style="width:60px;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse ($transactions as $i => $t)
                <tr class="{{ $t->is_void ? 'void-row' : '' }}">
                    <td class="center">{{ $i + 1 }}</td>
                    <td>
                        {{ $t->tanggal
                            ? str_pad($t->tanggal, 2, '0', STR_PAD_LEFT).'/'.substr(\App\Models\Transaction::monthLabel($t->bulan), 0, 3).'/'.$t->tahun
                            : $t->created_at->format('d/m/Y') }}
                    </td>
                    <td>{{ \App\Models\Transaction::monthLabel($t->bulan) }} {{ $t->tahun }}</td>
                    <td class="num" style="{{ $t->is_void ? 'text-decoration:line-through;' : '' }}">
                        Rp {{ number_format((float) $t->nominal, 0, ',', '.') }}
                    </td>
                    <td>{{ $t->petugas?->name ?: '-' }}</td>
                    <td class="center">
                        @if ($t->is_void)
                            <span class="badge-void">Dibatalkan</span>
                        @else
                            <span class="badge-ok">Lunas</span>
                            @php $grandTotal += (float) $t->nominal; @endphp
                        @endif
                    </td>
                    <td>
                        @if ($t->is_void && $t->void_reason)
                            {{ $t->void_reason }}
                        @else
                            {{ $t->catatan ?: '' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#9ca3af;padding:10px;">
                        Belum ada transaksi.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($transactions->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;font-weight:bold;padding-right:6px;">Total</td>
                    <td class="num">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="print-date">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>

    <div style="margin-top:40px; text-align:right; font-size:10px; font-family:Helvetica,Arial,sans-serif;">
        <p style="margin:0 0 4px;">________________, ______________________</p>
        <p style="margin:0 0 50px; text-align:center; width:220px; display:inline-block;">Mengetahui,</p>
        <br>
        <p style="margin:0; text-align:center; width:220px; display:inline-block;">________________________________________</p>
    </div>
</body>
</html>
