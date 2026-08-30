<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Job extends Model {
    protected static string $table = 'jobs';

    public static function getActive(int $limit = 10, int $offset = 0): array {
        return Database::fetchAll("SELECT * FROM jobs WHERE is_published = 1 AND (deadline IS NULL OR deadline >= CURDATE()) ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}");
    }
}
