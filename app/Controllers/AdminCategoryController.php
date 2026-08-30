<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;
use App\Models\Course;
use App\Services\CategoryService;

class AdminCategoryController extends Controller {

    /**
     * Category Hub: Tree View, Table View, Search & KPI Dashboard.
     */
    public function index(Request $request): void {
        $filters = [
            'q' => $request->input('q', ''),
            'status' => $request->input('status', ''),
            'is_featured' => $request->input('is_featured', ''),
            'view' => $request->input('view', 'tree') // 'tree' or 'table'
        ];

        $kpis = CategoryService::getKpiStats();
        $categories = CategoryService::getHierarchyTree($filters);
        $flatCategories = CategoryService::getFlatTreeList();
        $iconsCatalog = CategoryService::getCuratedIcons();

        $this->render('admin/categories/index', [
            'pageTitle' => 'Training Categories & Taxonomy',
            'categories' => $categories,
            'flatCategories' => $flatCategories,
            'kpis' => $kpis,
            'filters' => $filters,
            'iconsCatalog' => $iconsCatalog
        ], 'dashboard');
    }

    /**
     * 360° Category Detail Page with 6 tabs.
     */
    public function show(Request $request, int $id): void {
        $data = CategoryService::get360CategoryDetails($id);
        if (!$data) {
            $this->flash('danger', 'Category not found.');
            $this->redirect('admin/categories');
            return;
        }

        $allCategories = CategoryService::getFlatTreeList($id); // exclude self and descendants
        $iconsCatalog = CategoryService::getCuratedIcons();

        $this->render('admin/categories/show', array_merge($data, [
            'pageTitle' => 'Category: ' . $data['category']['name'],
            'allCategories' => $allCategories,
            'iconsCatalog' => $iconsCatalog
        ]), 'dashboard');
    }

    /**
     * Store Category with Image uploads and SEO.
     */
    public function store(Request $request): void {
        $thumbnail = null;
        $coverImage = null;

        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbnail = $this->uploadFile('thumbnail', 'categories');
        }

        // Handle cover image upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $coverImage = $this->uploadFile('cover_image', 'categories');
        }

        $data = [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'parent_id' => $request->input('parent_id'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
            'icon' => $request->input('icon', 'bi-cup-hot'),
            'color' => $request->input('color', '#4C3103'),
            'thumbnail' => $thumbnail,
            'cover_image' => $coverImage,
            'sort_order' => $request->input('sort_order', 0),
            'status' => $request->input('status', 'active'),
            'is_featured' => $request->input('is_featured', 0),
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'seo_keywords' => $request->input('seo_keywords'),
            'canonical_url' => $request->input('canonical_url')
        ];

        $res = CategoryService::createCategory($data);

        if (!$res['success']) {
            $this->flash('danger', $res['message']);
            $this->redirect('admin/categories');
            return;
        }

        $this->flash('success', 'Training category created successfully.');
        $this->redirect('admin/categories/' . $res['category_id']);
    }

    /**
     * Update Category details.
     */
    public function update(Request $request, int $id): void {
        $thumbnail = null;
        $coverImage = null;

        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbnail = $this->uploadFile('thumbnail', 'categories');
        }

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $coverImage = $this->uploadFile('cover_image', 'categories');
        }

        $data = [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'parent_id' => $request->input('parent_id'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description'),
            'icon' => $request->input('icon'),
            'color' => $request->input('color'),
            'sort_order' => $request->input('sort_order'),
            'status' => $request->input('status'),
            'is_featured' => $request->input('is_featured'),
            'seo_title' => $request->input('seo_title'),
            'seo_description' => $request->input('seo_description'),
            'seo_keywords' => $request->input('seo_keywords'),
            'canonical_url' => $request->input('canonical_url')
        ];

        if ($thumbnail) $data['thumbnail'] = $thumbnail;
        if ($coverImage) $data['cover_image'] = $coverImage;

        $res = CategoryService::updateCategory($id, $data);

        if (!$res['success']) {
            $this->flash('danger', $res['message']);
        } else {
            $this->flash('success', 'Category updated successfully.');
        }

        $this->redirect('admin/categories/' . $id);
    }

    /**
     * Duplicate Category.
     */
    public function duplicate(Request $request, int $id): void {
        $res = CategoryService::duplicateCategory($id);
        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/categories');
    }

    /**
     * Safe Deletion Prompt API.
     */
    public function deletePrompt(Request $request, int $id): void {
        $data = CategoryService::get360CategoryDetails($id);
        if (!$data) {
            Response::json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $targetCategories = CategoryService::getFlatTreeList($id);

        Response::json([
            'success' => true,
            'category' => $data['category'],
            'courses_count' => count($data['courses']),
            'subcategories_count' => count($data['subcategories']),
            'target_categories' => $targetCategories
        ]);
    }

    /**
     * Safe Deletion Execution.
     */
    public function delete(Request $request, int $id): void {
        $reassignCoursesTo = $request->input('reassign_courses_to') ? (int)$request->input('reassign_courses_to') : null;
        $reassignChildrenTo = $request->input('reassign_children_to') ? (int)$request->input('reassign_children_to') : null;
        $archiveOnly = !empty($request->input('archive_only'));

        $res = CategoryService::safeDeleteCategory($id, $reassignCoursesTo, $reassignChildrenTo, $archiveOnly);

        if ($request->isAjax()) {
            Response::json($res, $res['success'] ? 200 : 400);
        }

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/categories');
    }

    /**
     * Reassign Course to another Category.
     */
    public function reassignCourse(Request $request, int $id): void {
        $courseId = (int)$request->input('course_id');
        $newCategoryId = (int)$request->input('new_category_id');

        if ($courseId > 0 && $newCategoryId > 0) {
            $res = CategoryService::reassignCourse($courseId, $newCategoryId);
            $this->flash('success', 'Course reassigned successfully.');
        }

        $this->redirect('admin/categories/' . $id . '#tab-courses');
    }

    /**
     * Bulk Category Actions.
     */
    public function bulkAction(Request $request): void {
        $action = $request->input('bulk_action');
        $categoryIds = $request->input('category_ids', []);
        
        if (is_string($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        $payload = [
            'parent_id' => $request->input('bulk_parent_id')
        ];

        $res = CategoryService::executeBulkAction($action, $categoryIds, $payload);

        if ($request->isAjax()) {
            Response::json($res, $res['success'] ? 200 : 400);
        }

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/categories');
    }

    /**
     * Export Categories to CSV.
     */
    public function exportCsv(Request $request): void {
        $csv = CategoryService::exportCsv();
        $filename = 'bba_categories_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv;
        exit;
    }

    /**
     * Preview CSV Import.
     */
    public function importCsvPreview(Request $request): void {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'Please select a valid CSV file.'], 400);
        }

        $csvContent = file_get_contents($_FILES['csv_file']['tmp_name']);
        $preview = CategoryService::previewCsvImport($csvContent);

        Response::json($preview, $preview['success'] ? 200 : 400);
    }

    /**
     * Process CSV Import.
     */
    public function importCsvProcess(Request $request): void {
        $rows = $request->input('rows', []);
        if (empty($rows) || !is_array($rows)) {
            Response::json(['success' => false, 'message' => 'No valid rows to import.'], 400);
        }

        $res = CategoryService::processCsvImport($rows);
        Response::json($res);
    }

    /**
     * Helper for uploading category files.
     */
    private function uploadFile(string $field, string $folder): ?string {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$field];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $uploadDir = BASE_PATH . '/public/uploads/' . $folder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('cat_', true) . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $folder . '/' . $filename;
        }

        return null;
    }
}
