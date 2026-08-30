<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Role extends Model {
    protected static string $table = 'roles';

    public static function allWithCounts(): array {
        $sql = "SELECT r.*, COUNT(DISTINCT ur.user_id) as users_count,
                       COUNT(DISTINCT rp.permission_id) as permissions_count
                FROM roles r
                LEFT JOIN user_roles ur ON r.id = ur.role_id
                LEFT JOIN role_permissions rp ON r.id = rp.role_id
                GROUP BY r.id
                ORDER BY r.id ASC";
        return Database::fetchAll($sql);
    }

    public static function getPermissions(int $roleId): array {
        $sql = "SELECT p.*
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :rid
                ORDER BY p.module ASC, p.name ASC";
        return Database::fetchAll($sql, ['rid' => $roleId]);
    }

    public static function syncPermissions(int $roleId, array $permissionIds): void {
        Database::query("DELETE FROM role_permissions WHERE role_id = :rid", ['rid' => $roleId]);
        foreach ($permissionIds as $pId) {
            Database::insert('role_permissions', [
                'role_id' => $roleId,
                'permission_id' => (int)$pId
            ]);
        }
    }
}
