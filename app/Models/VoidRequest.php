<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaction_id', 'requested_by', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note'])]
class VoidRequest extends Model
{
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
