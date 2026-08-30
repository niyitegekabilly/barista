<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Invitation extends Model {
    protected static string $table = 'invitations';

    public static function findByToken(string $token): ?array {
        $sql = "SELECT i.*, r.name as role_name, r.slug as role_slug, c.name as cohort_name
                FROM invitations i
                JOIN roles r ON i.role_id = r.id
                LEFT JOIN cohorts c ON i.cohort_id = c.id
                WHERE i.token = :token AND i.status = 'pending' AND i.expires_at > NOW()
                LIMIT 1";
        return Database::fetchOne($sql, ['token' => $token]);
    }

    public static function allWithDetails(): array {
        $sql = "SELECT i.*, r.name as role_name, r.slug as role_slug, c.name as cohort_name,
                       u.name as inviter_name
                FROM invitations i
                JOIN roles r ON i.role_id = r.id
                LEFT JOIN cohorts c ON i.cohort_id = c.id
                LEFT JOIN users u ON i.invited_by = u.id
                ORDER BY i.created_at DESC";
        return Database::fetchAll($sql);
    }
}
