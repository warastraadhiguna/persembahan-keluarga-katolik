<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'kode_keluarga', 'qr_token', 'nama_kepala_keluarga', 'no_kk',
    'status_ekonomi', 'jml_anggota', 'status_rumah', 'no_hp', 'lingkungan_id',
    'is_active',
])]
class Family extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'jml_anggota' => 'integer',
            'is_active'   => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Family $family) {
            if (empty($family->kode_keluarga)) {
                $family->kode_keluarga = self::generateKodeKeluarga();
            }
            if (empty($family->qr_token)) {
                $family->qr_token = self::generateQrToken();
            }
        });
    }

    public static function generateKodeKeluarga(): string
    {
        $last = self::query()
            ->where('kode_keluarga', 'like', 'KK-%')
            ->orderByDesc('id')
            ->value('kode_keluarga');

        $nextNumber = $last ? ((int) substr($last, 3)) + 1 : 1;

        $kode = sprintf('KK-%05d', $nextNumber);

        // Pastikan unik meski ada race condition / gap
        while (self::where('kode_keluarga', $kode)->exists()) {
            $nextNumber++;
            $kode = sprintf('KK-%05d', $nextNumber);
        }

        return $kode;
    }

    public static function generateQrToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('qr_token', $token)->exists());

        return $token;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function lingkungan(): BelongsTo
    {
        return $this->belongsTo(Lingkungan::class);
    }

    /** No. KK ter-masking, hanya 4 digit terakhir tampil */
    public function getNoKkMaskedAttribute(): string
    {
        $noKk = (string) $this->no_kk;
        $length = strlen($noKk);

        if ($length <= 4) {
            return $noKk;
        }

        return str_repeat('*', $length - 4) . substr($noKk, -4);
    }
}
