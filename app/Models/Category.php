<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Category extends Model {
    protected static string $table = 'categories';

    public static function getActiveWithCounts(): array {
        $sql = "SELECT cat.*, (SELECT COUNT(*) FROM courses c WHERE c.category_id = cat.id AND c.is_published = 1) as courses_count
                FROM categories cat
                WHERE cat.is_active = 1
                ORDER BY cat.sort_order ASC";
        return Database::fetchAll($sql);
    }

    public function withCourseCount() {
        $sql = "SELECT cat.*, (SELECT COUNT(*) FROM courses c WHERE c.category_id = cat.id AND c.is_published = 1) as courses_count
                FROM categories cat
                ORDER BY cat.sort_order ASC";
        return Database::fetchAll($sql);
    }
}
