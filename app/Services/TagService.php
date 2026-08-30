<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Tag;
use App\Models\AuditLog;

class TagService {

    public static function createTag(string $name, ?string $description = null): array {
        $name = trim($name);
        if (empty($name)) {
            return ['success' => false, 'message' => 'Tag name is required.'];
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = trim($slug, '-');

        if (Tag::findBySlug($slug)) {
            return ['success' => false, 'message' => 'A tag with this name or slug already exists.'];
        }

        $tagId = Database::insert('tags', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'usage_count' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        AuditLog::log('tag_created', 'tag', $tagId, ['name' => $name]);

        return ['success' => true, 'tag_id' => $tagId, 'message' => 'Tag created successfully.'];
    }

    public static function updateTag(int $id, string $name, ?string $description = null): array {
        $tag = Tag::find($id);
        if (!$tag) {
            return ['success' => false, 'message' => 'Tag not found.'];
        }

        $name = trim($name);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));

        $existing = Tag::findBySlug($slug);
        if ($existing && (int)$existing['id'] !== $id) {
            return ['success' => false, 'message' => 'Tag slug is already in use by another tag.'];
        }

        Database::update('tags', [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        AuditLog::log('tag_updated', 'tag', $id, ['name' => $name]);

        return ['success' => true, 'message' => 'Tag updated successfully.'];
    }

    public static function deleteTag(int $id): array {
        Database::query("DELETE FROM course_tags WHERE tag_id = :id", ['id' => $id]);
        Database::query("DELETE FROM tags WHERE id = :id", ['id' => $id]);

        AuditLog::log('tag_deleted', 'tag', $id);
        return ['success' => true, 'message' => 'Tag deleted.'];
    }

    public static function searchTags(string $query, int $limit = 15): array {
        $q = '%' . trim($query) . '%';
        $sql = "SELECT id, name, slug, usage_count
                FROM tags
                WHERE name LIKE :q OR slug LIKE :q2
                ORDER BY usage_count DESC, name ASC
                LIMIT {$limit}";
        return Database::fetchAll($sql, ['q' => $q, 'q2' => $q]);
    }
}
