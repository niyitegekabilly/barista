<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Notification extends Model {
    protected static string $table = 'notifications';

    public static function send(int $userId, string $title, string $message, ?string $link = null): int {
        // Not using the base Model::create() here: it unconditionally injects
        // an `updated_at` value, but `notifications` has no such column —
        // that mismatch made every call to this method throw and silently
        // fail (the table had zero rows before this fix, despite existing
        // callers like CourseService::enroll() calling this on every signup).
        return Database::insert(static::$table, [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getUnread(int $userId, int $limit = 5): array {
        return self::where("user_id = :uid AND is_read = 0", ['uid' => $userId], 'id DESC', $limit);
    }
}
