<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class UserLogin extends Model {
    protected static string $table = 'user_logins';

    public static function log(int $userId, string $status = 'success'): void {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';

        Database::insert(static::$table, [
            'user_id' => $userId,
            'ip_address' => substr($ip, 0, 45),
            'user_agent' => substr($ua, 0, 255),
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Also update users table last_login_at
        if ($status === 'success') {
            Database::update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => substr($ip, 0, 45),
                'failed_login_attempts' => 0,
                'locked_until' => null
            ], ['id' => $userId]);
        }
    }

    public static function getRecentForUser(int $userId, int $limit = 15): array {
        return Database::fetchAll(
            "SELECT * FROM user_logins WHERE user_id = :uid ORDER BY created_at DESC LIMIT {$limit}",
            ['uid' => $userId]
        );
    }
}
