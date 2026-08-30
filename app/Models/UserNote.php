<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class UserNote extends Model {
    protected static string $table = 'user_notes';

    public static function getForUser(int $userId): array {
        $sql = "SELECT un.*, u.name as author_name, u.email as author_email
                FROM user_notes un
                LEFT JOIN users u ON un.author_id = u.id
                WHERE un.user_id = :uid
                ORDER BY un.created_at DESC";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }
}
