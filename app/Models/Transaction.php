<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'family_id', 'bulan', 'tahun', 'tanggal', 'nominal', 'catatan', 'is_kosong', 'bukti_foto', 'user_id',
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
            'is_kosong' => 'boolean',
            'voided_at' => 'datetime',
        ];
    }

    public function buktiFotoUrl(): ?string
    {
        return $this->bukti_foto ? asset('storage/'.$this->bukti_foto) : null;
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

    public function pendingVoidRequest(): HasOne
    {
        return $this->hasOne(VoidRequest::class)->where('status', 'pending');
    }
}
