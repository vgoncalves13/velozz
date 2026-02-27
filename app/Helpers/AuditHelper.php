<?php

namespace App\Helpers;

use App\Models\AuditLog;

class AuditHelper
{
    /**
     * Log an audit event
     *
     * @param  string  $action  Action performed (login, import, send_message, etc)
     * @param  string  $entity  Entity type (user, lead, whatsapp_message, etc)
     * @param  int|null  $entityId  Entity ID
     * @param  array|null  $previousData  Previous state of data
     * @param  array|null  $newData  New state of data
     * @param  int|null  $tenantId  Tenant ID (optional, auto-detected if null)
     * @param  int|null  $userId  User ID (optional, auto-detected if null)
     */
    public static function log(
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $previousData = null,
        ?array $newData = null,
        ?int $tenantId = null,
        ?int $userId = null
    ): AuditLog {
        // Get tenant_id from parameter or fallback to context
        $tenantId = $tenantId ?? auth()->user()?->tenant_id ?? tenant()?->id ?? null;

        // Get user_id from parameter or fallback to auth
        $userId = $userId ?? auth()->id();

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'previous_data' => $previousData,
            'new_data' => $newData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
