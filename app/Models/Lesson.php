<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Lesson extends Model {
    protected static string $table = 'lessons';

    public static function findWithResources(int $id): ?array {
        $lesson = Database::fetchOne("SELECT * FROM lessons WHERE id = :id LIMIT 1", ['id' => $id]);
        if ($lesson) {
            $lesson['resources'] = Database::fetchAll("SELECT * FROM lesson_resources WHERE lesson_id = :lid", ['lid' => $id]);
        }
        return $lesson;
    }
}
