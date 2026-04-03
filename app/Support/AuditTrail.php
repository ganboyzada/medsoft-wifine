<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditTrail
{
    public static function record(
        string $action,
        ?User $actor = null,
        ?int $organizationId = null,
        ?Model $subject = null,
        ?array $payload = null,
        ?Request $request = null
    ): void {
        AuditLog::query()->create([
            'organization_id' => $organizationId ?? $actor?->organization_id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
