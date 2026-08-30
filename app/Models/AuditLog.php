<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class AuditLog extends Model {
    protected static string $table = 'audit_logs';

    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?array $details = null): int {
        $userId = auth_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        return Database::insert(static::$table, [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details ? json_encode($details) : null,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
