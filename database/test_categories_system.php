<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/Session.php';
require BASE_PATH . '/app/Helpers/helpers.php';
require BASE_PATH . '/app/Core/Database.php';
require BASE_PATH . '/app/Core/Model.php';
require BASE_PATH . '/app/Models/Category.php';
require BASE_PATH . '/app/Models/Tag.php';
require BASE_PATH . '/app/Models/AuditLog.php';
require BASE_PATH . '/app/Services/CategoryService.php';
require BASE_PATH . '/app/Services/TagService.php';

echo "=========================================================\n";
echo "Beyond Barista Academy — Category & Taxonomy System Verification\n";
echo "=========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo "❌ [FAIL] {$name} - {$details}\n";
        $failed++;
    }
}

// 1. Test KPI Stats
$kpis = \App\Services\CategoryService::getKpiStats();
assertTest("KPI stats structure", isset($kpis['total'], $kpis['active'], $kpis['root_parents'], $kpis['subcategories'], $kpis['assigned_courses']), "Total: {$kpis['total']}");

// 2. Test Category Creation (Parent -> Child -> Grandchild)
$parentRes = \App\Services\CategoryService::createCategory([
    'name' => 'Sensory Skills Test ' . time(),
    'short_description' => 'Test parent category',
    'status' => 'active'
]);
assertTest("Create parent category", $parentRes['success'] === true, $parentRes['message'] ?? '');
$parentId = $parentRes['category_id'] ?? null;

$childRes = \App\Services\CategoryService::createCategory([
    'name' => 'Cupping Protocols Test ' . time(),
    'parent_id' => $parentId,
    'status' => 'active'
]);
assertTest("Create child category", $childRes['success'] === true, $childRes['message'] ?? '');
$childId = $childRes['category_id'] ?? null;

$grandchildRes = \App\Services\CategoryService::createCategory([
    'name' => 'Triangulation Test ' . time(),
    'parent_id' => $childId,
    'status' => 'active'
]);
assertTest("Create grandchild category (3-level nesting)", $grandchildRes['success'] === true, $grandchildRes['message'] ?? '');
$grandchildId = $grandchildRes['category_id'] ?? null;

// 3. Test Circular Dependency Prevention
$circularAttempt = \App\Services\CategoryService::updateCategory($parentId, [
    'parent_id' => $grandchildId // Trying to make parent a child of its grandchild!
]);
assertTest("Circular parent check prevents loop", $circularAttempt['success'] === false, "Circular parent was rejected as expected: " . ($circularAttempt['message'] ?? ''));

// 4. Test Breadcrumbs
$breadcrumbs = \App\Models\Category::getBreadcrumbs($grandchildId);
assertTest("Breadcrumb path generated", count($breadcrumbs) === 3 && $breadcrumbs[0]['id'] == $parentId, "Breadcrumbs length: " . count($breadcrumbs));

// 5. Test Flat Tree with Indentation
$flatList = \App\Services\CategoryService::getFlatTreeList();
assertTest("Flat tree list generation", count($flatList) > 0 && isset($flatList[0]['indented_name']), "Flat items count: " . count($flatList));

// 6. Test Tag Creation & Synchronization
$tagRes = \App\Services\TagService::createTag('Test Cupping Tag ' . time(), 'Test description');
assertTest("Tag creation", $tagRes['success'] === true, $tagRes['message'] ?? '');
$tagId = $tagRes['tag_id'] ?? null;

$popularTags = \App\Models\Tag::getPopular(5);
assertTest("Popular tags query", is_array($popularTags), "Popular tags returned");

// 7. Test Safe Deletion Workflow with Course Reassignment
$targetCat = \App\Services\CategoryService::createCategory([
    'name' => 'Target Reassignment Cat ' . time(),
    'status' => 'active'
]);
$targetId = $targetCat['category_id'];

$firstUser = \App\Core\Database::fetchOne("SELECT id FROM users LIMIT 1");
$userId = $firstUser['id'] ?? 1;

// Create temporary course assigned to child
$courseId = \App\Core\Database::insert('courses', [
    'title' => 'Temp Test Course ' . time(),
    'slug' => 'temp-test-course-' . time(),
    'category_id' => $childId,
    'price' => 0,
    'level' => 'beginner',
    'created_by' => $userId,
    'created_at' => date('Y-m-d H:i:s')
]);
\App\Core\Database::insert('course_categories', [
    'course_id' => $courseId,
    'category_id' => $childId,
    'is_primary' => 1
]);

// Attempt delete without reassignment
$failedDelete = \App\Services\CategoryService::safeDeleteCategory($childId);
assertTest("Safe deletion requires course reassignment", $failedDelete['success'] === false && !empty($failedDelete['require_reassign']), "Protected from accidental deletion");

// Execute safe deletion with reassignment
$successDelete = \App\Services\CategoryService::safeDeleteCategory($childId, $targetId);
assertTest("Safe deletion moves course to target category", $successDelete['success'] === true, $successDelete['message'] ?? '');

$updatedCourse = \App\Core\Database::fetchOne("SELECT category_id FROM courses WHERE id = :id", ['id' => $courseId]);
assertTest("Course category_id successfully reassigned", (int)$updatedCourse['category_id'] === $targetId, "New category_id: " . ($updatedCourse['category_id'] ?? 'null'));

// 8. Test CSV Export & Import Validation
$csv = \App\Services\CategoryService::exportCsv();
assertTest("CSV export contains data", !empty($csv) && str_contains($csv, 'Name') && str_contains($csv, 'Slug'), "CSV Length: " . strlen($csv));

$sampleCsv = "name,parent,icon,status,featured\nBrewing Dynamics,,bi-funnel,active,1\nAeroPress Masterclass,Brewing Dynamics,bi-cup-hot,active,0\n";
$importPreview = \App\Services\CategoryService::previewCsvImport($sampleCsv);
assertTest("CSV import preview validation", $importPreview['success'] === true && $importPreview['valid_count'] >= 1, "Valid count: " . ($importPreview['valid_count'] ?? 0));

// Cleanup test records
\App\Core\Database::query("DELETE FROM courses WHERE id = :id", ['id' => $courseId]);
\App\Core\Database::query("DELETE FROM categories WHERE id IN ({$parentId}, {$grandchildId}, {$targetId})");
if ($tagId) \App\Core\Database::query("DELETE FROM tags WHERE id = :id", ['id' => $tagId]);

echo "\n=========================================================\n";
echo "SUMMARY: Passed: {$passed}, Failed: {$failed}\n";
echo "=========================================================\n";
