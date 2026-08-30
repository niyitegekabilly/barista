<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Notification extends Model {
    protected static string $table = 'notifications';

    public static function send(int $userId, string $title, string $message, ?string $link = null): int {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0
        ]);
    }

    public static function getUnread(int $userId, int $limit = 5): array {
        return self::where("user_id = :uid AND is_read = 0", ['uid' => $userId], 'id DESC', $limit);
    }
}
