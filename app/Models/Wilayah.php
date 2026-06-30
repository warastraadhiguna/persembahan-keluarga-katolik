<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama'])]
class Wilayah extends Model
{
    public function lingkungans(): HasMany
    {
        return $this->hasMany(Lingkungan::class);
    }
}
