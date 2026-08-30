<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Category extends Model {
    protected static string $table = 'categories';

    /**
     * Get all active categories with published course count.
     */
    public static function getActiveWithCounts(): array {
        $sql = "SELECT cat.*,
                       (SELECT COUNT(DISTINCT cc.course_id)
                        FROM course_categories cc
                        JOIN courses c ON cc.course_id = c.id
                        WHERE cc.category_id = cat.id AND c.is_published = 1) as courses_count,
                       (SELECT COUNT(*) FROM categories child WHERE child.parent_id = cat.id AND child.status = 'active') as subcategories_count
                FROM categories cat
                WHERE cat.status = 'active'
                ORDER BY cat.sort_order ASC, cat.name ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Alias for getActiveWithCounts to support backward compatibility.
     */
    public function withCourseCount(): array {
        return static::getActiveWithCounts();
    }

    /**
     * Get single category with parent and subcategories.
     */
    public static function findWithDetails(int $id): ?array {
        $sql = "SELECT cat.*, parent.name as parent_name, parent.slug as parent_slug,
                       u1.name as creator_name, u2.name as updater_name
                FROM categories cat
                LEFT JOIN categories parent ON cat.parent_id = parent.id
                LEFT JOIN users u1 ON cat.created_by = u1.id
                LEFT JOIN users u2 ON cat.updated_by = u2.id
                WHERE cat.id = :id LIMIT 1";
        $category = Database::fetchOne($sql, ['id' => $id]);
        if ($category) {
            $category['subcategories'] = static::getChildren($id);
            $category['breadcrumbs'] = static::getBreadcrumbs($id);
        }
        return $category;
    }

    /**
     * Find category by slug.
     */
    public static function findBySlug(string $slug): ?array {
        $sql = "SELECT cat.*, parent.name as parent_name, parent.slug as parent_slug
                FROM categories cat
                LEFT JOIN categories parent ON cat.parent_id = parent.id
                WHERE cat.slug = :slug LIMIT 1";
        $category = Database::fetchOne($sql, ['slug' => $slug]);
        if ($category) {
            $category['subcategories'] = static::getChildren((int)$category['id']);
            $category['breadcrumbs'] = static::getBreadcrumbs((int)$category['id']);
        }
        return $category;
    }

    /**
     * Get direct children of a category.
     */
    public static function getChildren(int $parentId): array {
        $sql = "SELECT cat.*,
                       (SELECT COUNT(DISTINCT cc.course_id)
                        FROM course_categories cc
                        JOIN courses c ON cc.course_id = c.id
                        WHERE cc.category_id = cat.id AND c.is_published = 1) as courses_count
                FROM categories cat
                WHERE cat.parent_id = :pid AND cat.status != 'archived'
                ORDER BY cat.sort_order ASC, cat.name ASC";
        return Database::fetchAll($sql, ['pid' => $parentId]);
    }

    /**
     * Get all descendants IDs recursively.
     */
    public static function getDescendantIds(int $categoryId): array {
        $descendants = [];
        $children = Database::fetchAll("SELECT id FROM categories WHERE parent_id = :pid", ['pid' => $categoryId]);
        foreach ($children as $child) {
            $childId = (int)$child['id'];
            $descendants[] = $childId;
            $descendants = array_merge($descendants, static::getDescendantIds($childId));
        }
        return $descendants;
    }

    /**
     * Check if a candidate parent is a descendant (to prevent circular references).
     */
    public static function isDescendant(int $categoryId, int $candidateParentId): bool {
        if ($categoryId === $candidateParentId) {
            return true;
        }
        $descendants = static::getDescendantIds($categoryId);
        return in_array($candidateParentId, $descendants, true);
    }

    /**
     * Build breadcrumb trail from root to current category.
     */
    public static function getBreadcrumbs(int $categoryId): array {
        $breadcrumbs = [];
        $currentId = $categoryId;
        $visited = [];

        while ($currentId && !in_array($currentId, $visited)) {
            $visited[] = $currentId;
            $cat = Database::fetchOne("SELECT id, name, slug, parent_id FROM categories WHERE id = :id", ['id' => $currentId]);
            if (!$cat) break;
            array_unshift($breadcrumbs, $cat);
            $currentId = $cat['parent_id'] ? (int)$cat['parent_id'] : null;
        }

        return $breadcrumbs;
    }

    /**
     * Log category lifecycle event.
     */
    public static function logActivity(int $categoryId, string $action, array $details = []): void {
        $userId = auth_id();
        Database::insert('category_activity_logs', [
            'category_id' => $categoryId,
            'user_id' => $userId ?: null,
            'action' => $action,
            'details' => json_encode($details),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get recent activity logs for this category.
     */
    public static function getActivityLogs(int $categoryId, int $limit = 20): array {
        $sql = "SELECT cal.*, u.name as user_name, u.email as user_email
                FROM category_activity_logs cal
                LEFT JOIN users u ON cal.user_id = u.id
                WHERE cal.category_id = :cid
                ORDER BY cal.created_at DESC
                LIMIT {$limit}";
        return Database::fetchAll($sql, ['cid' => $categoryId]);
    }
}
