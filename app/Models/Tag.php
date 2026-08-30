<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Tag extends Model {
    protected static string $table = 'tags';

    public static function findBySlug(string $slug): ?array {
        return Database::fetchOne("SELECT * FROM tags WHERE slug = :slug LIMIT 1", ['slug' => $slug]);
    }

    public static function allWithCourseCount(): array {
        $sql = "SELECT t.*, COUNT(DISTINCT ct.course_id) as courses_count
                FROM tags t
                LEFT JOIN course_tags ct ON t.id = ct.tag_id
                GROUP BY t.id
                ORDER BY courses_count DESC, t.name ASC";
        return Database::fetchAll($sql);
    }

    public static function getPopular(int $limit = 10): array {
        $sql = "SELECT t.*, COUNT(DISTINCT ct.course_id) as courses_count
                FROM tags t
                JOIN course_tags ct ON t.id = ct.tag_id
                GROUP BY t.id
                ORDER BY courses_count DESC
                LIMIT {$limit}";
        return Database::fetchAll($sql);
    }

    public static function getUnused(): array {
        $sql = "SELECT t.*, 0 as courses_count
                FROM tags t
                LEFT JOIN course_tags ct ON t.id = ct.tag_id
                WHERE ct.course_id IS NULL
                ORDER BY t.name ASC";
        return Database::fetchAll($sql);
    }

    public static function getForCourse(int $courseId): array {
        $sql = "SELECT t.*
                FROM tags t
                JOIN course_tags ct ON t.id = ct.tag_id
                WHERE ct.course_id = :cid
                ORDER BY t.name ASC";
        return Database::fetchAll($sql, ['cid' => $courseId]);
    }

    public static function syncForCourse(int $courseId, array $tagNamesOrIds): void {
        Database::query("DELETE FROM course_tags WHERE course_id = :cid", ['cid' => $courseId]);
        
        foreach ($tagNamesOrIds as $item) {
            $item = trim((string)$item);
            if (empty($item)) continue;

            $tagId = null;
            if (is_numeric($item)) {
                $tagId = (int)$item;
            } else {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $item));
                $tag = static::findBySlug($slug);
                if ($tag) {
                    $tagId = (int)$tag['id'];
                } else {
                    $tagId = Database::insert('tags', [
                        'name' => $item,
                        'slug' => $slug,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            if ($tagId) {
                Database::insert('course_tags', [
                    'course_id' => $courseId,
                    'tag_id' => $tagId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Recalculate usage counts
        Database::query("UPDATE tags t SET usage_count = (SELECT COUNT(*) FROM course_tags ct WHERE ct.tag_id = t.id)");
    }
}
