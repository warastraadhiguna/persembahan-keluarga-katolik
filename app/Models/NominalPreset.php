<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['urutan', 'label', 'nominal', 'is_active'])]
class NominalPreset extends Model
{
    protected function casts(): array
    {
        return [
            'nominal'   => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function active()
    {
        return self::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nominal')
            ->get();
    }

    public function getLabelFormattedAttribute(): string
    {
        return $this->label ?: number_format($this->nominal, 0, ',', '.');
    }
}
