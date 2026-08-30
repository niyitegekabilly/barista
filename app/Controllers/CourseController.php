<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Review;
use App\Models\Enrollment;

class CourseController extends Controller
{
    public function index(): void
    {
        $filter   = $this->request->input('category');
        $search   = $this->request->input('q') ?: $this->request->input('search');
        $level    = $this->request->input('level');
        $isFree   = $this->request->input('is_free');
        $sort     = $this->request->input('sort', 'popular');

        $whereConditions = ["c.is_published = 1"];
        $params = [];

        if ($filter) {
            $whereConditions[] = "cat.slug = :cat";
            $params['cat'] = $filter;
        }
        if ($search) {
            $whereConditions[] = "(c.title LIKE :q OR c.description LIKE :q2)";
            $params['q'] = "%$search%";
            $params['q2'] = "%$search%";
        }
        if ($level && $level !== 'all') {
            $whereConditions[] = "c.level = :level";
            $params['level'] = $level;
        }
        if ($isFree !== null && $isFree !== '') {
            $whereConditions[] = "c.is_free = :is_free";
            $params['is_free'] = (int)$isFree;
        }

        $whereClause = implode(' AND ', $whereConditions);

        $query = $this->db()->query(
            "SELECT c.*, cat.name category_name, u.name instructor_name,
                    COUNT(DISTINCT e.id) as students_count,
                    COUNT(DISTINCT e.id) as enrollment_count,
                    COALESCE(AVG(r.rating), 0) as rating_avg,
                    COALESCE(AVG(r.rating), 0) as avg_rating
             FROM courses c
             LEFT JOIN categories cat ON c.category_id = cat.id
             LEFT JOIN users u ON c.created_by = u.id
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN reviews r ON r.course_id = c.id
             WHERE {$whereClause}
             GROUP BY c.id
             ORDER BY " . ($sort === 'newest' ? 'c.created_at DESC' : ($sort === 'price_asc' ? 'c.price ASC' : 'enrollment_count DESC')),
            $params
        );

        $courses    = $query->fetchAll();
        $categories = (new Category())->getActiveWithCounts();
        $totalCount = count($courses);
        $filters    = ['category' => $filter, 'search' => $search, 'level' => $level, 'is_free' => $isFree];

        $this->render('public/courses/index', compact('courses', 'categories', 'filter', 'search', 'level', 'sort', 'totalCount', 'filters'));
    }

    public function show(\App\Core\Request $request, string $slug): void
    {
        $course = $this->db()->fetchOne(
            "SELECT c.*, cat.name category_name, cat.slug category_slug, u.name instructor_name, up.headline instructor_headline,
                    COUNT(DISTINCT e.id) enrollment_count, AVG(r.rating) avg_rating, COUNT(DISTINCT r.id) review_count
             FROM courses c
             LEFT JOIN categories cat ON c.category_id = cat.id
             LEFT JOIN users u ON c.created_by = u.id
             LEFT JOIN user_profiles up ON u.id = up.user_id
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN reviews r ON r.course_id = c.id
             WHERE c.slug = ? AND c.is_published = 1
             GROUP BY c.id",
            [$slug]
        );

        if (!$course) {
            $this->abort(404);
            return;
        }

        // Fetch modules (MariaDB 10.4 compatible — no JSON_ARRAYAGG)
        $modules = $this->db()->query(
            "SELECT m.* FROM modules m WHERE m.course_id = ? ORDER BY m.sort_order",
            [$course['id']]
        )->fetchAll();

        // Fetch all lessons for this course at once, then group by module in PHP
        $allLessons = $this->db()->query(
            "SELECT l.id, l.module_id, l.title, l.lesson_type, l.duration_minutes, l.is_free_preview, l.sort_order
             FROM lessons l WHERE l.course_id = ? ORDER BY l.module_id, l.sort_order",
            [$course['id']]
        )->fetchAll();

        // Index lessons by module_id
        $lessonsByModule = [];
        foreach ($allLessons as $lesson) {
            $lessonsByModule[$lesson['module_id']][] = $lesson;
        }

        // Attach lessons array to each module
        foreach ($modules as &$module) {
            $module['lessons'] = $lessonsByModule[$module['id']] ?? [];
            $module['quizzes'] = []; // quizzes fetched per-module when student is in classroom
        }
        unset($module);

        $reviews    = [];  // No reviews yet — review model requires student relation
        $isEnrolled = false;

        if ($user = auth()) {
            $isEnrolled = (bool)$this->db()->fetchOne(
                "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?",
                [$user['id'], $course['id']]
            );
        }

        // Compute derived fields the view expects
        $course['rating_avg']     = (float)($course['avg_rating'] ?? 0);
        $course['reviews_count']  = (int)($course['review_count'] ?? 0);
        $course['students_count'] = (int)($course['enrollment_count'] ?? 0);
        $course['lessons_count']  = count($allLessons);

        $this->render('public/courses/show', compact('course', 'modules', 'reviews', 'isEnrolled'));
    }

    public function enroll(\App\Core\Request $request, int $id): void
    {
        $user = auth();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $course = $this->db()->fetchOne("SELECT * FROM courses WHERE id = ?", [$id]);
        if (!$course) {
            $this->abort(404);
            return;
        }

        // Check if already enrolled
        $existing = $this->db()->fetchOne(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?",
            [$user['id'], $id]
        );

        if (!$existing) {
            $this->db()->insert('enrollments', [
                'user_id'          => $user['id'],
                'course_id'        => $id,
                'status'           => 'in_progress',
                'progress_percent' => 0,
                'enrolled_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        $this->flash('success', 'You are successfully enrolled! Welcome to ' . $course['title']);
        $this->redirect('/student/classroom/' . $course['slug']);
    }
}
