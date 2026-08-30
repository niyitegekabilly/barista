<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Review extends Model {
    protected static string $table = 'reviews';

    public static function getByCourse(int $courseId): array {
        $sql = "SELECT r.*, u.name as user_name, p.avatar as user_avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE r.course_id = :cid AND r.is_approved = 1
                ORDER BY r.created_at DESC";
        return Database::fetchAll($sql, ['cid' => $courseId]);
    }
}
