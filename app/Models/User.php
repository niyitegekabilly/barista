<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model {
    protected static string $table = 'users';

    public static function findWithProfile(int $id): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug,
                       p.phone, p.headline, p.bio, p.avatar, p.country, p.city, p.language, p.social_links
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE u.id = :id LIMIT 1";
        $user = Database::fetchOne($sql, ['id' => $id]);
        if ($user) {
            $user['roles'] = static::getRoles($id);
            $user['cohorts'] = static::getCohorts($id);
        }
        return $user;
    }

    public static function findByEmail(string $email): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email LIMIT 1";
        return Database::fetchOne($sql, ['email' => $email]);
    }

    public static function findByStudentId(string $studentId): ?array {
        return Database::fetchOne("SELECT * FROM users WHERE student_id = :sid LIMIT 1", ['sid' => $studentId]);
    }

    public static function findByInstructorId(string $instructorId): ?array {
        return Database::fetchOne("SELECT * FROM users WHERE instructor_id = :iid LIMIT 1", ['iid' => $instructorId]);
    }

    public static function getRoles(int $userId): array {
        $sql = "SELECT r.*, ur.is_primary
                FROM roles r
                JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :uid
                ORDER BY ur.is_primary DESC, r.name ASC";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }

    public static function getCohorts(int $userId): array {
        $sql = "SELECT c.*, cu.role_in_cohort, cu.enrolled_at as cohort_enrolled_at
                FROM cohorts c
                JOIN cohort_users cu ON c.id = cu.cohort_id
                WHERE cu.user_id = :uid
                ORDER BY c.start_date DESC";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }

    public static function getPermissions(int $userId): array {
        $sql = "SELECT DISTINCT p.*
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :uid";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }

    public static function hasPermission(int $userId, string $permissionSlug): bool {
        // Super admin has all permissions
        $user = static::find($userId);
        if ($user && ($user['role_id'] == 1 || static::hasRole($userId, 'super_admin'))) {
            return true;
        }

        $sql = "SELECT COUNT(*) cnt
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = :uid AND p.slug = :slug";
        $res = Database::fetchOne($sql, ['uid' => $userId, 'slug' => $permissionSlug]);
        return ($res['cnt'] ?? 0) > 0;
    }

    public static function hasRole(int $userId, string $roleSlug): bool {
        $sql = "SELECT COUNT(*) cnt
                FROM roles r
                JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :uid AND r.slug = :slug";
        $res = Database::fetchOne($sql, ['uid' => $userId, 'slug' => $roleSlug]);
        return ($res['cnt'] ?? 0) > 0;
    }

    public static function syncRoles(int $userId, array $roleIds, int $primaryRoleId): void {
        Database::query("DELETE FROM user_roles WHERE user_id = :uid", ['uid' => $userId]);
        
        // Ensure primary role is included
        if (!in_array($primaryRoleId, $roleIds)) {
            $roleIds[] = $primaryRoleId;
        }

        foreach ($roleIds as $rId) {
            Database::insert('user_roles', [
                'user_id' => $userId,
                'role_id' => (int)$rId,
                'is_primary' => ((int)$rId === (int)$primaryRoleId) ? 1 : 0
            ]);
        }

        // Keep legacy role_id in sync
        Database::update(static::$table, ['role_id' => $primaryRoleId], ['id' => $userId]);
    }

    public static function syncCohorts(int $userId, array $cohortIds): void {
        Database::query("DELETE FROM cohort_users WHERE user_id = :uid", ['uid' => $userId]);
        foreach ($cohortIds as $cId) {
            Database::insert('cohort_users', [
                'cohort_id' => (int)$cId,
                'user_id' => $userId,
                'role_in_cohort' => 'student',
                'enrolled_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public static function generateStudentId(int $userId): string {
        return sprintf("BBA-STU-%s-%04d", date('Y'), $userId);
    }

    public static function generateInstructorId(int $userId): string {
        return sprintf("BBA-INS-%04d", $userId);
    }
}
