<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PrintSetting extends Model
{
    protected $fillable = [
        'rows', 'cols', 'start',
        'paper', 'paper_width', 'paper_height',
        'margin', 'gap',
    ];

    protected $casts = [
        'rows'         => 'integer',
        'cols'         => 'integer',
        'start'        => 'integer',
        'paper_width'  => 'decimal:1',
        'paper_height' => 'decimal:1',
        'margin'       => 'decimal:1',
        'gap'          => 'decimal:1',
    ];

    private const CACHE_KEY = 'print_settings.current';

    private const DEFAULTS = [
        'rows' => 8, 'cols' => 3, 'start' => 1,
        'paper' => 'a4', 'paper_width' => 210, 'paper_height' => 297,
        'margin' => 10, 'gap' => 0,
    ];

    public static function current(): self
    {
        $attributes = Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()->firstOrCreate(['id' => 1], self::DEFAULTS)->getAttributes();
        });

        return (new self)->forceFill($attributes)->syncOriginal();
    }

    public static function updateSettings(array $data): self
    {
        $setting = self::query()->firstOrCreate(['id' => 1], self::DEFAULTS);
        $setting->update($data);

        Cache::forget(self::CACHE_KEY);

        return $setting;
    }
}
