<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Module extends Model {
    protected static string $table = 'modules';

    public static function getByCourse(int $courseId): array {
        return Database::fetchAll("SELECT * FROM modules WHERE course_id = :cid ORDER BY sort_order ASC", ['cid' => $courseId]);
    }
}
