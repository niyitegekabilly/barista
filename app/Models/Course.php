<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Course extends Model {
    protected static string $table = 'courses';

    public static function findBySlug(string $slug): ?array {
        $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                       u.name as instructor_name, p.avatar as instructor_avatar, p.headline as instructor_headline, p.bio as instructor_bio,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as students_count,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as lessons_count,
                       (SELECT COALESCE(AVG(r.rating), 5.0) FROM reviews r WHERE r.course_id = c.id AND r.is_approved = 1) as rating_avg,
                       (SELECT COUNT(*) FROM reviews r WHERE r.course_id = c.id AND r.is_approved = 1) as reviews_count
                FROM courses c
                JOIN categories cat ON c.category_id = cat.id
                JOIN users u ON c.created_by = u.id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE c.slug = :slug AND c.is_published = 1
                LIMIT 1";
        return Database::fetchOne($sql, ['slug' => $slug]);
    }

    public static function getCatalog(array $filters = [], string $orderBy = 'c.id DESC', int $limit = 12, int $offset = 0): array {
        $where = ["c.is_published = 1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "cat.slug = :cat_slug";
            $params['cat_slug'] = $filters['category'];
        }

        if (!empty($filters['level']) && $filters['level'] !== 'all') {
            $where[] = "c.level = :level";
            $params['level'] = $filters['level'];
        }

        if (isset($filters['is_free']) && $filters['is_free'] !== '') {
            $where[] = "c.is_free = :is_free";
            $params['is_free'] = (int) $filters['is_free'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(c.title LIKE :search OR c.short_description LIKE :search OR c.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                       u.name as instructor_name, p.avatar as instructor_avatar,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as students_count,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as lessons_count,
                       (SELECT COALESCE(AVG(r.rating), 5.0) FROM reviews r WHERE r.course_id = c.id AND r.is_approved = 1) as rating_avg
                FROM courses c
                JOIN categories cat ON c.category_id = cat.id
                JOIN users u ON c.created_by = u.id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}";

        return Database::fetchAll($sql, $params);
    }

    public static function countCatalog(array $filters = []): int {
        $where = ["c.is_published = 1"];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = "cat.slug = :cat_slug";
            $params['cat_slug'] = $filters['category'];
        }

        if (!empty($filters['level']) && $filters['level'] !== 'all') {
            $where[] = "c.level = :level";
            $params['level'] = $filters['level'];
        }

        if (isset($filters['is_free']) && $filters['is_free'] !== '') {
            $where[] = "c.is_free = :is_free";
            $params['is_free'] = (int) $filters['is_free'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(c.title LIKE :search OR c.short_description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*)
                FROM courses c
                JOIN categories cat ON c.category_id = cat.id
                WHERE {$whereClause}";

        return (int) Database::fetchValue($sql, $params);
    }

    public static function getCurriculum(int $courseId): array {
        $modules = Database::fetchAll("SELECT * FROM modules WHERE course_id = :cid ORDER BY sort_order ASC", ['cid' => $courseId]);
        foreach ($modules as &$mod) {
            $mod['lessons'] = Database::fetchAll("SELECT * FROM lessons WHERE module_id = :mid AND is_published = 1 ORDER BY sort_order ASC", ['mid' => $mod['id']]);
            $mod['quizzes'] = Database::fetchAll("SELECT * FROM quizzes WHERE module_id = :mid AND is_published = 1", ['mid' => $mod['id']]);
        }
        return $modules;
    }
}
