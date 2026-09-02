<?php

namespace App\Domain\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

final class AuditLogger
{
    public function log(
        string $action,
        ?object $subject = null,
        array $properties = [],
        string $actorType = 'system',
        ?int $actorUserId = null,
        ?string $correlationId = null,
    ): AuditLog {
        $filtered = $this->redact($properties);

        return AuditLog::query()->create([
            'actor_user_id' => $actorUserId,
            'actor_type' => $actorType,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject->id ?? null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'properties' => $filtered,
            'correlation_id' => $correlationId,
            'created_at' => now(),
        ]);
    }

    private function redact(array $properties): array
    {
        $sensitive = ['private_key', 'seed', 'mnemonic', 'secret', 'api_secret', 'password', 'token', 'key_ref'];

        array_walk_recursive($properties, function (&$value, $key) use ($sensitive) {
            foreach ($sensitive as $needle) {
                if (stripos((string) $key, $needle) !== false) {
                    $value = '[redacted]';
                }
            }
        });

        return $properties;
    }
}
