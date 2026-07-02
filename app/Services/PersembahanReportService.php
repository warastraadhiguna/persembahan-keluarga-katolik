<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\Wilayah;
use Illuminate\Support\Collection;

class PersembahanReportService
{
    // $dateFrom and $dateTo are 'Y-m-d' strings (e.g. '2026-07-01')
    public function monthly(string $dateFrom, string $dateTo, ?int $wilayahId, ?int $lingkunganId, ?int $userId, string $statusFilter = ''): Collection
    {
        $from = \Carbon\Carbon::parse($dateFrom);
        $to   = \Carbon\Carbon::parse($dateTo);

        // Integer comparison: tahun*10000 + bulan*100 + tanggal
        $fromInt      = $from->year * 10000 + $from->month * 100 + $from->day;
        $toInt        = $to->year   * 10000 + $to->month   * 100 + $to->day;
        $fromMonthInt = $from->year * 100 + $from->month;
        $toMonthInt   = $to->year   * 100 + $to->month;

        $rows = Family::query()
            ->with('lingkungan.wilayah')
            ->when($wilayahId, fn ($q) => $q->whereHas('lingkungan', fn ($q2) => $q2->where('wilayah_id', $wilayahId)))
            ->when($lingkunganId, fn ($q) => $q->where('lingkungan_id', $lingkunganId))
            ->with(['transactions' => function ($q) use ($fromInt, $toInt, $fromMonthInt, $toMonthInt, $userId) {
                $q->where(function ($q2) use ($fromInt, $toInt, $fromMonthInt, $toMonthInt) {
                    // Transaksi dengan tanggal: perbandingan tanggal penuh
                    $q2->where(function ($q3) use ($fromInt, $toInt) {
                        $q3->whereNotNull('tanggal')
                            ->whereRaw('(tahun * 10000 + bulan * 100 + tanggal) >= ?', [$fromInt])
                            ->whereRaw('(tahun * 10000 + bulan * 100 + tanggal) <= ?', [$toInt]);
                    })
                    // Transaksi tanpa tanggal: perbandingan level bulan
                    ->orWhere(function ($q3) use ($fromMonthInt, $toMonthInt) {
                        $q3->whereNull('tanggal')
                            ->whereRaw('(tahun * 100 + bulan) >= ?', [$fromMonthInt])
                            ->whereRaw('(tahun * 100 + bulan) <= ?', [$toMonthInt]);
                    });
                })
                ->where('is_void', false)
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->with('petugas:id,name');
            }])
            ->orderBy('nama_kepala_keluarga')
            ->get()
            ->map(function (Family $family) {
                $nominal = (float) $family->transactions->sum('nominal');

                return [
                    'family'      => $family,
                    'nominal'     => $nominal,
                    'sudah_bayar' => $nominal > 0,
                    'petugas'     => $family->transactions
                        ->pluck('petugas.name')
                        ->filter()
                        ->unique()
                        ->implode(', '),
                ];
            });

        if ($statusFilter === 'sudah_bayar') {
            $rows = $rows->where('sudah_bayar', true)->values();
        } elseif ($statusFilter === 'belum_bayar') {
            $rows = $rows->where('sudah_bayar', false)->values();
        }

        return $rows;
    }

    public function yearly(int $tahun, ?int $wilayahId, ?int $lingkunganId, ?int $userId): Collection
    {
        return Family::query()
            ->with('lingkungan.wilayah')
            ->when($wilayahId, fn ($q) => $q->whereHas('lingkungan', fn ($q2) => $q2->where('wilayah_id', $wilayahId)))
            ->when($lingkunganId, fn ($q) => $q->where('lingkungan_id', $lingkunganId))
            ->with(['transactions' => function ($q) use ($tahun, $userId) {
                $q->where('tahun', $tahun)
                    ->where('is_void', false)
                    ->when($userId, fn ($q) => $q->where('user_id', $userId));
            }])
            ->orderBy('nama_kepala_keluarga')
            ->get()
            ->map(function (Family $family) {
                $perBulan = array_fill(1, 12, 0.0);

                foreach ($family->transactions as $transaction) {
                    $perBulan[$transaction->bulan] += (float) $transaction->nominal;
                }

                return [
                    'family'    => $family,
                    'per_bulan' => $perBulan,
                    'total'     => array_sum($perBulan),
                ];
            });
    }

    public function wilayahOptions(): Collection
    {
        return Wilayah::query()->orderBy('nama')->get();
    }

    public function lingkunganOptions(): Collection
    {
        return Lingkungan::query()->orderBy('nama')->get();
    }
}
