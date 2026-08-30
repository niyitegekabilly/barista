<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Event extends Model {
    protected static string $table = 'events';

    public static function getUpcoming(int $limit = 6): array {
        return Database::fetchAll("SELECT * FROM events WHERE is_published = 1 AND start_date >= NOW() ORDER BY start_date ASC LIMIT {$limit}");
    }
}
