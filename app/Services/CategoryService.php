<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Category;
use App\Models\AuditLog;

class CategoryService {

    /**
     * Get KPI summary statistics for Category Management Dashboard.
     */
    public static function getKpiStats(): array {
        $total = (int)(Database::fetchValue("SELECT COUNT(*) FROM categories WHERE status != 'archived'") ?: 0);
        $active = (int)(Database::fetchValue("SELECT COUNT(*) FROM categories WHERE status = 'active'") ?: 0);
        $rootParents = (int)(Database::fetchValue("SELECT COUNT(*) FROM categories WHERE parent_id IS NULL AND status != 'archived'") ?: 0);
        $subcategories = (int)(Database::fetchValue("SELECT COUNT(*) FROM categories WHERE parent_id IS NOT NULL AND status != 'archived'") ?: 0);
        
        $assignedCourses = (int)(Database::fetchValue("SELECT COUNT(DISTINCT course_id) FROM course_categories") ?: 0);
        $emptyCategories = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM categories cat 
             WHERE status != 'archived' AND NOT EXISTS (SELECT 1 FROM course_categories cc WHERE cc.category_id = cat.id)"
        ) ?: 0);

        return [
            'total' => $total,
            'active' => $active,
            'root_parents' => $rootParents,
            'subcategories' => $subcategories,
            'assigned_courses' => $assignedCourses,
            'empty_categories' => $emptyCategories
        ];
    }

    /**
     * Build nested category hierarchy tree.
     */
    public static function getHierarchyTree(array $filters = []): array {
        $conditions = ["cat.status != 'archived'"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(cat.name LIKE :q OR cat.description LIKE :q2 OR cat.slug LIKE :q3)";
            $params['q'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
            $params['q3'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "cat.status = :status";
            $params['status'] = $filters['status'];
        }

        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $conditions[] = "cat.is_featured = :feat";
            $params['feat'] = (int)$filters['is_featured'];
        }

        $whereSql = implode(' AND ', $conditions);

        $sql = "SELECT cat.*,
                       (SELECT COUNT(DISTINCT cc.course_id) FROM course_categories cc JOIN courses c ON cc.course_id = c.id WHERE cc.category_id = cat.id AND c.is_published = 1) as courses_count,
                       (SELECT COUNT(DISTINCT e.id) FROM enrollments e JOIN courses c ON e.course_id = c.id JOIN course_categories cc ON cc.course_id = c.id WHERE cc.category_id = cat.id) as students_count
                FROM categories cat
                WHERE {$whereSql}
                ORDER BY cat.sort_order ASC, cat.name ASC";

        $allCategories = Database::fetchAll($sql, $params);

        // If searching with keyword, return flat list or grouped
        if (!empty($filters['q'])) {
            return $allCategories;
        }

        return static::buildTree($allCategories, null);
    }

    /**
     * Recursive tree node builder.
     */
    private static function buildTree(array &$categories, ?int $parentId = null, int $depth = 0): array {
        $branch = [];
        foreach ($categories as $category) {
            $catParentId = $category['parent_id'] ? (int)$category['parent_id'] : null;
            if ($catParentId === $parentId) {
                $category['depth'] = $depth;
                $children = static::buildTree($categories, (int)$category['id'], $depth + 1);
                $category['children'] = $children;
                $category['children_count'] = count($children);
                $branch[] = $category;
            }
        }
        return $branch;
    }

    /**
     * Get a flat list of categories with indentation prefixes for dropdown menus.
     * Prevents circular parent assignment by excluding descendants if $excludeId is provided.
     */
    public static function getFlatTreeList(?int $excludeId = null): array {
        $sql = "SELECT id, name, slug, parent_id, status, is_featured, sort_order
                FROM categories
                WHERE status != 'archived'
                ORDER BY sort_order ASC, name ASC";
        $all = Database::fetchAll($sql);

        $excludedIds = [];
        if ($excludeId) {
            $excludedIds = array_merge([$excludeId], Category::getDescendantIds($excludeId));
        }

        $filtered = array_filter($all, fn($c) => !in_array((int)$c['id'], $excludedIds, true));

        $tree = static::buildTree($filtered, null);
        $flat = [];
        static::flattenTree($tree, $flat);

        return $flat;
    }

    private static function flattenTree(array $tree, array &$flat): void {
        foreach ($tree as $node) {
            $children = $node['children'] ?? [];
            unset($node['children']);
            $node['indented_name'] = str_repeat('— ', $node['depth']) . $node['name'];
            $flat[] = $node;
            if (!empty($children)) {
                static::flattenTree($children, $flat);
            }
        }
    }

    /**
     * Get 360° Comprehensive Category Detail Page Data.
     */
    public static function get360CategoryDetails(int $categoryId): ?array {
        $category = Category::findWithDetails($categoryId);
        if (!$category) {
            return null;
        }

        // 1. Courses assigned to this category (primary & secondary)
        $coursesSql = "SELECT c.*, cc.is_primary, u.name as instructor_name,
                              COUNT(DISTINCT e.id) as students_count,
                              AVG(r.rating) as avg_rating,
                              (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) as lessons_count
                       FROM courses c
                       JOIN course_categories cc ON c.id = cc.course_id
                       LEFT JOIN users u ON c.created_by = u.id
                       LEFT JOIN enrollments e ON e.course_id = c.id
                       LEFT JOIN reviews r ON r.course_id = c.id
                       WHERE cc.category_id = :cid
                       GROUP BY c.id
                       ORDER BY cc.is_primary DESC, c.created_at DESC";
        $courses = Database::fetchAll($coursesSql, ['cid' => $categoryId]);

        // 2. Subcategories
        $subcategories = Category::getChildren($categoryId);

        // 3. Analytics Aggregation
        $totalCourses = count($courses);
        $totalEnrollments = array_sum(array_column($courses, 'students_count'));
        $avgRating = $totalCourses > 0 ? (float)array_sum(array_filter(array_column($courses, 'avg_rating'))) / max(1, count(array_filter(array_column($courses, 'avg_rating')))) : 0;
        
        $revenueSql = "SELECT SUM(o.total_amount) as total_rev
                       FROM orders o
                       JOIN order_items oi ON o.id = oi.order_id
                       JOIN course_categories cc ON oi.course_id = cc.course_id
                       WHERE cc.category_id = :cid AND o.status = 'completed'";
        $totalRevenue = (float)(Database::fetchValue($revenueSql, ['cid' => $categoryId]) ?: 0);

        // Top Course
        $topCourse = null;
        if (!empty($courses)) {
            $sorted = $courses;
            usort($sorted, fn($a, $b) => ($b['students_count'] ?? 0) <=> ($a['students_count'] ?? 0));
            $topCourse = $sorted[0] ?? null;
        }

        // 4. Activity Logs
        $activityLogs = Category::getActivityLogs($categoryId, 25);

        return [
            'category' => $category,
            'courses' => $courses,
            'subcategories' => $subcategories,
            'analytics' => [
                'total_courses' => $totalCourses,
                'total_enrollments' => $totalEnrollments,
                'total_revenue' => $totalRevenue,
                'avg_rating' => round($avgRating, 1),
                'top_course' => $topCourse
            ],
            'activity_logs' => $activityLogs
        ];
    }

    /**
     * Create new Category with validation, slug generation, and activity logging.
     */
    public static function createCategory(array $data): array {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return ['success' => false, 'message' => 'Category name is required.'];
        }

        $slug = !empty($data['slug']) ? static::generateUniqueSlug($data['slug']) : static::generateUniqueSlug($name);
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        $insertData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'icon' => $data['icon'] ?? 'bi-cup-hot',
            'color' => $data['color'] ?? '#4C3103',
            'thumbnail' => $data['thumbnail'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'parent_id' => $parentId,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'status' => in_array($data['status'] ?? '', ['draft', 'active', 'inactive', 'archived']) ? $data['status'] : 'active',
            'is_featured' => !empty($data['is_featured']) ? 1 : 0,
            'seo_title' => $data['seo_title'] ?? $name,
            'seo_description' => $data['seo_description'] ?? ($data['short_description'] ?? null),
            'seo_keywords' => $data['seo_keywords'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'created_by' => auth_id() ?: 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $categoryId = Database::insert('categories', $insertData);

        Category::logActivity($categoryId, 'category_created', [
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId
        ]);

        AuditLog::log('category_created', 'category', $categoryId, ['name' => $name]);

        return ['success' => true, 'category_id' => $categoryId, 'slug' => $slug];
    }

    /**
     * Update Category details and validate against circular hierarchy loops.
     */
    public static function updateCategory(int $id, array $data): array {
        $category = Category::find($id);
        if (!$category) {
            return ['success' => false, 'message' => 'Category not found.'];
        }

        $name = trim($data['name'] ?? $category['name']);
        $newParentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        // Circular hierarchy check
        if ($newParentId) {
            if ($newParentId === $id) {
                return ['success' => false, 'message' => 'A category cannot be its own parent.'];
            }
            if (Category::isDescendant($id, $newParentId)) {
                return ['success' => false, 'message' => 'Invalid parent hierarchy: cannot set a subcategory as a parent of its ancestor.'];
            }
        }

        $slug = !empty($data['slug']) && $data['slug'] !== $category['slug']
            ? static::generateUniqueSlug($data['slug'], $id)
            : $category['slug'];

        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? $category['description'],
            'short_description' => $data['short_description'] ?? $category['short_description'],
            'icon' => $data['icon'] ?? $category['icon'],
            'color' => $data['color'] ?? $category['color'],
            'parent_id' => $newParentId,
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : $category['sort_order'],
            'status' => in_array($data['status'] ?? '', ['draft', 'active', 'inactive', 'archived']) ? $data['status'] : $category['status'],
            'is_featured' => isset($data['is_featured']) ? (!empty($data['is_featured']) ? 1 : 0) : $category['is_featured'],
            'seo_title' => $data['seo_title'] ?? $category['seo_title'],
            'seo_description' => $data['seo_description'] ?? $category['seo_description'],
            'seo_keywords' => $data['seo_keywords'] ?? $category['seo_keywords'],
            'canonical_url' => $data['canonical_url'] ?? $category['canonical_url'],
            'updated_by' => auth_id() ?: 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['thumbnail'])) {
            $updateData['thumbnail'] = $data['thumbnail'];
        }
        if (!empty($data['cover_image'])) {
            $updateData['cover_image'] = $data['cover_image'];
        }

        Database::update('categories', $updateData, ['id' => $id]);

        Category::logActivity($id, 'category_updated', [
            'old_name' => $category['name'],
            'new_name' => $name,
            'parent_changed' => $category['parent_id'] != $newParentId
        ]);

        AuditLog::log('category_updated', 'category', $id, ['name' => $name]);

        return ['success' => true, 'message' => 'Category updated successfully.'];
    }

    /**
     * Duplicate a Category structure.
     */
    public static function duplicateCategory(int $id): array {
        $source = Category::find($id);
        if (!$source) {
            return ['success' => false, 'message' => 'Source category not found.'];
        }

        $newName = $source['name'] . ' (Copy)';
        $newSlug = static::generateUniqueSlug($source['slug'] . '-copy');

        $newId = Database::insert('categories', [
            'name' => $newName,
            'slug' => $newSlug,
            'description' => $source['description'],
            'short_description' => $source['short_description'],
            'icon' => $source['icon'],
            'color' => $source['color'],
            'thumbnail' => $source['thumbnail'],
            'cover_image' => $source['cover_image'],
            'parent_id' => $source['parent_id'],
            'sort_order' => ((int)$source['sort_order']) + 1,
            'status' => 'draft',
            'is_featured' => 0,
            'seo_title' => $newName,
            'created_by' => auth_id() ?: 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Category::logActivity($newId, 'category_duplicated', ['source_id' => $id]);

        return ['success' => true, 'category_id' => $newId, 'message' => 'Category duplicated as draft.'];
    }

    /**
     * Safe Deletion Workflow with Course and Child Reassignment.
     */
    public static function safeDeleteCategory(int $categoryId, ?int $reassignCoursesTo = null, ?int $reassignChildrenTo = null, bool $archiveOnly = false): array {
        $category = Category::find($categoryId);
        if (!$category) {
            return ['success' => false, 'message' => 'Category not found.'];
        }

        // Count linked courses and subcategories
        $coursesCount = (int)Database::fetchValue("SELECT COUNT(DISTINCT course_id) FROM course_categories WHERE category_id = :cid", ['cid' => $categoryId]);
        $subcategoriesCount = (int)Database::fetchValue("SELECT COUNT(*) FROM categories WHERE parent_id = :cid", ['cid' => $categoryId]);

        // Option 1: Archive instead of hard delete
        if ($archiveOnly) {
            Database::update('categories', ['status' => 'archived', 'updated_at' => date('Y-m-d H:i:s')], ['id' => $categoryId]);
            Category::logActivity($categoryId, 'category_archived');
            AuditLog::log('category_archived', 'category', $categoryId);
            return ['success' => true, 'message' => 'Category archived successfully. Course relationships preserved.'];
        }

        // Option 2: Require reassignment if courses or subcategories exist
        if ($coursesCount > 0 && !$reassignCoursesTo) {
            return [
                'success' => false,
                'require_reassign' => true,
                'courses_count' => $coursesCount,
                'subcategories_count' => $subcategoriesCount,
                'message' => "Category contains {$coursesCount} course(s). Please specify a target category to reassign them to before deleting."
            ];
        }

        // Execute Course Reassignment
        if ($coursesCount > 0 && $reassignCoursesTo) {
            // Update primary category_id on courses
            Database::query("UPDATE courses SET category_id = :new_cid WHERE category_id = :old_cid", [
                'new_cid' => $reassignCoursesTo,
                'old_cid' => $categoryId
            ]);

            // Re-point course_categories pivot
            Database::query("UPDATE IGNORE course_categories SET category_id = :new_cid WHERE category_id = :old_cid", [
                'new_cid' => $reassignCoursesTo,
                'old_cid' => $categoryId
            ]);
            Database::query("DELETE FROM course_categories WHERE category_id = :old_cid", ['old_cid' => $categoryId]);

            Category::logActivity($reassignCoursesTo, 'courses_reassigned_from_deleted_category', [
                'from_category_id' => $categoryId,
                'count' => $coursesCount
            ]);
        }

        // Execute Subcategories Reassignment or promote to root
        if ($subcategoriesCount > 0) {
            Database::query("UPDATE categories SET parent_id = :new_pid WHERE parent_id = :old_pid", [
                'new_pid' => $reassignChildrenTo ?: null,
                'old_pid' => $categoryId
            ]);
        }

        // Delete activity logs and category record
        Database::query("DELETE FROM category_activity_logs WHERE category_id = :cid", ['cid' => $categoryId]);
        Database::query("DELETE FROM categories WHERE id = :cid", ['cid' => $categoryId]);

        AuditLog::log('category_deleted', 'category', $categoryId, [
            'name' => $category['name'],
            'reassigned_courses_to' => $reassignCoursesTo
        ]);

        return ['success' => true, 'message' => 'Category safely deleted and courses reassigned.'];
    }

    /**
     * Reassign a single course to another category.
     */
    public static function reassignCourse(int $courseId, int $newCategoryId): array {
        Database::update('courses', ['category_id' => $newCategoryId], ['id' => $courseId]);
        
        Database::query("DELETE FROM course_categories WHERE course_id = :cid AND is_primary = 1", ['cid' => $courseId]);
        Database::insert('course_categories', [
            'course_id' => $courseId,
            'category_id' => $newCategoryId,
            'is_primary' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Category::logActivity($newCategoryId, 'course_assigned', ['course_id' => $courseId]);
        return ['success' => true, 'message' => 'Course reassigned successfully.'];
    }

    /**
     * Execute Bulk Category Actions.
     */
    public static function executeBulkAction(string $action, array $categoryIds, array $payload = []): array {
        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));
        if (empty($categoryIds)) {
            return ['success' => false, 'message' => 'No categories selected.'];
        }

        $inClause = implode(',', $categoryIds);
        $count = count($categoryIds);

        switch ($action) {
            case 'activate':
                Database::query("UPDATE categories SET status = 'active' WHERE id IN ($inClause)");
                AuditLog::log('bulk_categories_activated', 'category', null, ['count' => $count]);
                return ['success' => true, 'message' => "Activated {$count} categories."];

            case 'deactivate':
                Database::query("UPDATE categories SET status = 'inactive' WHERE id IN ($inClause)");
                AuditLog::log('bulk_categories_deactivated', 'category', null, ['count' => $count]);
                return ['success' => true, 'message' => "Deactivated {$count} categories."];

            case 'archive':
                Database::query("UPDATE categories SET status = 'archived' WHERE id IN ($inClause)");
                AuditLog::log('bulk_categories_archived', 'category', null, ['count' => $count]);
                return ['success' => true, 'message' => "Archived {$count} categories."];

            case 'change_parent':
                $newParentId = !empty($payload['parent_id']) ? (int)$payload['parent_id'] : null;
                foreach ($categoryIds as $cid) {
                    if ($newParentId && ($newParentId === $cid || Category::isDescendant($cid, $newParentId))) {
                        continue; // Skip invalid circular assignments
                    }
                    Database::update('categories', ['parent_id' => $newParentId], ['id' => $cid]);
                }
                AuditLog::log('bulk_categories_parent_changed', 'category', null, ['count' => $count, 'parent_id' => $newParentId]);
                return ['success' => true, 'message' => "Updated hierarchy for {$count} categories."];

            default:
                return ['success' => false, 'message' => 'Unknown bulk action.'];
        }
    }

    /**
     * Export Categories to CSV.
     */
    public static function exportCsv(): string {
        $sql = "SELECT cat.*, parent.name as parent_name,
                       (SELECT COUNT(DISTINCT cc.course_id) FROM course_categories cc WHERE cc.category_id = cat.id) as courses_count
                FROM categories cat
                LEFT JOIN categories parent ON cat.parent_id = parent.id
                WHERE cat.status != 'archived'
                ORDER BY cat.parent_id ASC, cat.sort_order ASC";
        $categories = Database::fetchAll($sql);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID', 'Name', 'Slug', 'Parent Category', 'Description', 'Short Description', 'Icon', 'Color', 'Status', 'Featured', 'Display Order', 'Courses Count']);

        foreach ($categories as $c) {
            fputcsv($output, [
                $c['id'],
                $c['name'],
                $c['slug'],
                $c['parent_name'] ?? 'None (Root)',
                $c['description'] ?? '',
                $c['short_description'] ?? '',
                $c['icon'] ?? 'bi-cup-hot',
                $c['color'] ?? '#4C3103',
                strtoupper($c['status']),
                $c['is_featured'] ? 'Yes' : 'No',
                $c['sort_order'],
                $c['courses_count']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    /**
     * Parse and Preview Categories CSV Import.
     */
    public static function previewCsvImport(string $csvContent): array {
        $lines = array_filter(array_map('trim', explode("\n", $csvContent)));
        if (empty($lines)) {
            return ['success' => false, 'message' => 'CSV file is empty.'];
        }

        $headerLine = array_shift($lines);
        $headers = array_map(fn($h) => strtolower(trim($h, " \t\n\r\0\x0B\"'")), str_getcsv($headerLine));

        if (!in_array('name', $headers)) {
            return ['success' => false, 'message' => 'CSV must include at least a "name" column header.'];
        }

        $existingCategories = [];
        foreach (Database::fetchAll("SELECT id, name, slug FROM categories") as $c) {
            $existingCategories[strtolower($c['name'])] = (int)$c['id'];
            $existingCategories[strtolower($c['slug'])] = (int)$c['id'];
        }

        $previewRows = [];
        $validCount = 0;
        $errorCount = 0;

        foreach ($lines as $idx => $line) {
            if (empty(trim($line))) continue;
            $rowValues = str_getcsv($line);
            $rowData = [];
            foreach ($headers as $hIndex => $hKey) {
                $rowData[$hKey] = $rowValues[$hIndex] ?? '';
            }

            $name = trim($rowData['name'] ?? '');
            $parent = trim($rowData['parent'] ?? ($rowData['parent_category'] ?? ''));
            $status = strtolower(trim($rowData['status'] ?? 'active'));
            $icon = trim($rowData['icon'] ?? 'bi-cup-hot');
            $featured = !empty($rowData['featured']) && in_array(strtolower($rowData['featured']), ['1', 'yes', 'true']) ? 1 : 0;

            $errors = [];
            if (empty($name)) {
                $errors[] = 'Name is required';
            }

            $parentId = null;
            if (!empty($parent) && strtolower($parent) !== 'none') {
                $parentId = $existingCategories[strtolower($parent)] ?? null;
                if (!$parentId) {
                    $errors[] = "Parent category '{$parent}' does not exist";
                }
            }

            $isValid = empty($errors);
            if ($isValid) $validCount++; else $errorCount++;

            $previewRows[] = [
                'row_number' => $idx + 2,
                'name' => $name,
                'parent' => $parent,
                'parent_id' => $parentId,
                'status' => in_array($status, ['draft', 'active', 'inactive']) ? $status : 'active',
                'icon' => $icon,
                'is_featured' => $featured,
                'is_valid' => $isValid,
                'errors' => $errors
            ];
        }

        return [
            'success' => true,
            'total_rows' => count($previewRows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'rows' => $previewRows
        ];
    }

    /**
     * Process Validated CSV Rows into Database.
     */
    public static function processCsvImport(array $rows): array {
        $imported = 0;
        $failed = 0;

        foreach ($rows as $r) {
            if (empty($r['is_valid'])) {
                $failed++;
                continue;
            }

            $res = static::createCategory([
                'name' => $r['name'],
                'parent_id' => $r['parent_id'] ?? null,
                'icon' => $r['icon'] ?? 'bi-cup-hot',
                'status' => $r['status'] ?? 'active',
                'is_featured' => !empty($r['is_featured']) ? 1 : 0
            ]);

            if ($res['success']) $imported++; else $failed++;
        }

        AuditLog::log('csv_categories_imported', 'category', null, ['imported' => $imported, 'failed' => $failed]);
        return ['success' => true, 'imported' => $imported, 'failed' => $failed];
    }

    /**
     * Generate unique URL slug.
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $base = trim($base, '-');
        if (empty($base)) $base = 'category';

        $slug = $base;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM categories WHERE slug = :slug" . ($excludeId ? " AND id != {$excludeId}" : "");
            $exists = Database::fetchOne($sql, ['slug' => $slug]);
            if (!$exists) {
                return $slug;
            }
            $slug = $base . '-' . $counter;
            $counter++;
        }
    }

    /**
     * Curated Icon Library for Specialty Coffee & Hospitality LMS.
     */
    public static function getCuratedIcons(): array {
        return [
            'Coffee & Barista' => [
                'bi-cup-hot' => 'Hot Coffee Cup',
                'bi-cup-hot-fill' => 'Hot Cup Fill',
                'bi-cup-straw' => 'Iced Coffee / Cold Brew',
                'bi-fire' => 'Roasting & Heat Science',
                'bi-droplet-half' => 'Extraction & Moisture',
                'bi-water' => 'Brewing & Water Chemistry',
                'bi-flower1' => 'Sensory & Floral Notes',
                'bi-funnel' => 'V60 / Drip Filtration',
                'bi-thermometer-half' => 'Milk Steaming Temp',
                'bi-clock-history' => 'Brew Time & Extraction',
                'bi-gear-wide-connected' => 'Grinder Calibration',
                'bi-speedometer2' => 'Pressure & Flow Dynamics',
            ],
            'Hospitality & Service' => [
                'bi-person-check-fill' => 'Guest Service Standards',
                'bi-building' => 'Café & Hotel Operations',
                'bi-shop' => 'Coffee Shop Leadership',
                'bi-shield-check' => 'Food Safety & Hygiene',
                'bi-currency-exchange' => 'Cost Control & Pricing',
                'bi-receipt' => 'POS & Inventory Systems',
                'bi-briefcase-fill' => 'Hospitality Careers',
                'bi-people-fill' => 'Team & Shift Management',
            ],
            'Academy & Certification' => [
                'bi-award-fill' => 'SCA Certification',
                'bi-patch-check-fill' => 'Verified Barista Diploma',
                'bi-mortarboard-fill' => 'Masterclass Curriculum',
                'bi-journal-code' => 'Recipe Manuals & Guides',
                'bi-stars' => 'Latte Art Competition',
                'bi-lightning-charge-fill' => 'Speed & Workflow Efficiency',
                'bi-bar-chart-line-fill' => 'Sensory Score Sheets',
                'bi-trophy-fill' => 'Championship Preparation',
            ]
        ];
    }
}
