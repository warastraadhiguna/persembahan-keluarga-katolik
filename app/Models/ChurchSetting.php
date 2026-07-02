<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChurchSetting extends Model
{
    protected $fillable = ['nama', 'alamat', 'telepon', 'email', 'website'];

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1], ['nama' => '']);
    }
}
