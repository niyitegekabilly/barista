<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class LessonDiscussion extends Model {
    protected static string $table = 'lesson_discussions';

    public static function getThreadedForLesson(int $lessonId): array {
        $threads = Database::fetchAll(
            "SELECT ld.*, u.name as user_name, r.slug as user_role, up.avatar as user_avatar
             FROM lesson_discussions ld
             JOIN users u ON ld.user_id = u.id
             LEFT JOIN roles r ON u.role_id = r.id
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE ld.lesson_id = :lid AND ld.parent_id IS NULL
             ORDER BY ld.created_at DESC",
            ['lid' => $lessonId]
        );

        foreach ($threads as &$t) {
            $t['replies'] = Database::fetchAll(
                "SELECT ld.*, u.name as user_name, r.slug as user_role, up.avatar as user_avatar
                 FROM lesson_discussions ld
                 JOIN users u ON ld.user_id = u.id
                 LEFT JOIN roles r ON u.role_id = r.id
                 LEFT JOIN user_profiles up ON up.user_id = u.id
                 WHERE ld.parent_id = :pid
                 ORDER BY ld.created_at ASC",
                ['pid' => $t['id']]
            );
        }

        return $threads;
    }
}
