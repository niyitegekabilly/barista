<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Cohort extends Model {
    protected static string $table = 'cohorts';

    public static function allWithMemberCount(): array {
        $sql = "SELECT c.*, COUNT(DISTINCT cu.user_id) as members_count
                FROM cohorts c
                LEFT JOIN cohort_users cu ON c.id = cu.cohort_id
                GROUP BY c.id
                ORDER BY c.created_at DESC";
        return Database::fetchAll($sql);
    }

    public static function getMembers(int $cohortId): array {
        $sql = "SELECT u.id, u.name, u.email, u.student_id, u.status, cu.role_in_cohort, cu.enrolled_at,
                       p.avatar, p.phone
                FROM users u
                JOIN cohort_users cu ON u.id = cu.user_id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE cu.cohort_id = :cid
                ORDER BY cu.enrolled_at DESC";
        return Database::fetchAll($sql, ['cid' => $cohortId]);
    }
}
