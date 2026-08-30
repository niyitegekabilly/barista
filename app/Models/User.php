<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model {
    protected static string $table = 'users';

    public static function findWithProfile(int $id): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug,
                       p.phone, p.headline, p.bio, p.avatar, p.country, p.city, p.language
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE u.id = :id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email LIMIT 1";
        return Database::fetchOne($sql, ['email' => $email]);
    }
}
