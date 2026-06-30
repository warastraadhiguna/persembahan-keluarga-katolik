<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'action', 'subject_type', 'subject_id', 'description', 'old_data', 'new_data',
])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public const ACTIONS = [
        'transaction.created' => 'Transaksi Dicatat',
        'transaction.voided'  => 'Transaksi Dibatalkan',
        'family.created'      => 'Keluarga Ditambahkan',
        'family.updated'      => 'Keluarga Diperbarui',
        'auth.login'          => 'Login',
    ];

    protected function casts(): array
    {
        return [
            'old_data' => 'array',
            'new_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }
}
