<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'family_id', 'bulan', 'tahun', 'nominal', 'catatan', 'user_id',
    'is_void', 'void_reason', 'voided_by', 'voided_at',
])]
class Transaction extends Model
{
    use HasFactory;

    public const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function monthLabel(int $bulan): string
    {
        return self::MONTHS[$bulan] ?? (string) $bulan;
    }

    protected function casts(): array
    {
        return [
            'nominal'   => 'decimal:2',
            'is_void'   => 'boolean',
            'voided_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
