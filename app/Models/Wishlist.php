<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Wishlist extends Model {
    protected static string $table = 'wishlists';

    public static function has(int $userId, int $courseId): bool {
        return (bool) Database::fetchValue("SELECT 1 FROM wishlists WHERE user_id = :uid AND course_id = :cid LIMIT 1", [
            'uid' => $userId,
            'cid' => $courseId
        ]);
    }

    public static function toggle(int $userId, int $courseId): bool {
        if (self::has($userId, $courseId)) {
            Database::delete('wishlists', "user_id = :uid AND course_id = :cid", ['uid' => $userId, 'cid' => $courseId]);
            return false; // removed
        } else {
            Database::insert('wishlists', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return true; // added
        }
    }

    public static function getUserWishlist(int $userId): array {
        $sql = "SELECT c.*, cat.name as category_name, u.name as instructor_name,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as lessons_count,
                       (SELECT COALESCE(AVG(r.rating), 5.0) FROM reviews r WHERE r.course_id = c.id AND r.is_approved = 1) as rating_avg
                FROM wishlists w
                JOIN courses c ON w.course_id = c.id
                JOIN categories cat ON c.category_id = cat.id
                JOIN users u ON c.created_by = u.id
                WHERE w.user_id = :uid AND c.is_published = 1
                ORDER BY w.created_at DESC";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }
}
