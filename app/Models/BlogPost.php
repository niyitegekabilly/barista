<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class BlogPost extends Model {
    protected static string $table = 'blog_posts';

    public static function getPublished(int $limit = 10, int $offset = 0): array {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.name as author_name
                FROM blog_posts p
                JOIN blog_categories c ON p.category_id = c.id
                JOIN users u ON p.user_id = u.id
                WHERE p.is_published = 1
                ORDER BY p.published_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return Database::fetchAll($sql);
    }

    public static function findBySlug(string $slug): ?array {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, u.name as author_name,
                       prof.avatar as author_avatar, prof.headline as author_headline
                FROM blog_posts p
                JOIN blog_categories c ON p.category_id = c.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN user_profiles prof ON u.id = prof.user_id
                WHERE p.slug = :slug AND p.is_published = 1
                LIMIT 1";
        return Database::fetchOne($sql, ['slug' => $slug]);
    }
}
