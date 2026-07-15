<?php

namespace App\Betting\Services;

use App\Betting\Models\AuditLog;
use App\Betting\Models\Market;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BettingAuditService
{
    public function log(
        Model $auditable,
        ?string $previousStatus,
        string $newStatus,
        ?User $actor = null,
        ?string $reason = null,
        array $metadata = [],
        string $actorType = 'user'
    ): AuditLog {
        return AuditLog::create([
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? $actorType : 'system',
            'reason' => $reason,
            'metadata' => $metadata ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function logMarketTransition(
        Market $market,
        ?string $previousStatus,
        string $newStatus,
        ?User $actor = null,
        ?string $reason = null,
        array $metadata = []
    ): AuditLog {
        return $this->log($market, $previousStatus, $newStatus, $actor, $reason, $metadata, $actor?->role === 'admin' ? 'admin' : 'user');
    }
}
