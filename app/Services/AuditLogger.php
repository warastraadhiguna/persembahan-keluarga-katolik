<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $old = [], array $new = []): AuditLog
    {
        return AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'description'  => $description,
            'old_data'     => $old ?: null,
            'new_data'     => $new ?: null,
        ]);
    }
}
