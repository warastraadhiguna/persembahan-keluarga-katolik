<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Lingkungan;
use App\Models\Wilayah;
use Illuminate\Support\Collection;

class PersembahanReportService
{
    public function monthly(int $bulan, int $tahun, ?int $wilayahId, ?int $lingkunganId, ?int $userId): Collection
    {
        return Family::query()
            ->with('lingkungan.wilayah')
            ->when($wilayahId, fn ($q) => $q->whereHas('lingkungan', fn ($q2) => $q2->where('wilayah_id', $wilayahId)))
            ->when($lingkunganId, fn ($q) => $q->where('lingkungan_id', $lingkunganId))
            ->with(['transactions' => function ($q) use ($bulan, $tahun, $userId) {
                $q->where('bulan', $bulan)
                    ->where('tahun', $tahun)
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
