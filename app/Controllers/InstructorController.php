<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;

class InstructorController extends Controller
{
    public function dashboard(): void
    {
        $instructorId = auth()['id'];

        $stats = [
            'total_courses'  => (int)($this->db()->fetchOne("SELECT COUNT(*) cnt FROM courses WHERE created_by = ?", [$instructorId])['cnt'] ?? 0),
            'total_students' => (int)($this->db()->fetchOne("SELECT COUNT(DISTINCT e.user_id) cnt FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.created_by = ?", [$instructorId])['cnt'] ?? 0),
            'avg_rating'     => (float)($this->db()->fetchOne("SELECT AVG(r.rating) avg FROM reviews r JOIN courses c ON r.course_id = c.id WHERE c.created_by = ?", [$instructorId])['avg'] ?? 0),
            'total_earnings' => (float)($this->db()->fetchOne("SELECT SUM(oi.price) total FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN courses c ON oi.item_id = c.id WHERE oi.item_type = 'course' AND c.created_by = ? AND o.status = 'completed'", [$instructorId])['total'] ?? 0),
        ];

        $courses = $this->db()->query(
            "SELECT c.*, COUNT(DISTINCT e.id) enrollment_count, AVG(r.rating) avg_rating, cat.name category_name
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN reviews r ON r.course_id = c.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             WHERE c.created_by = ? GROUP BY c.id ORDER BY c.created_at DESC LIMIT 10",
            [$instructorId]
        )->fetchAll();

        $recent_reviews = $this->db()->query(
            "SELECT r.*, u.name student_name, c.title course_title
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             JOIN courses c ON r.course_id = c.id
             WHERE c.created_by = ? ORDER BY r.created_at DESC LIMIT 5",
            [$instructorId]
        )->fetchAll();

        $this->render('instructor/dashboard', compact('stats', 'courses', 'recent_reviews'), 'dashboard');
    }

    public function courses(): void
    {
        $instructorId = auth()['id'];
        $courses = $this->db()->query(
            "SELECT c.*, COUNT(DISTINCT e.id) enrollment_count, AVG(r.rating) avg_rating, cat.name category_name
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN reviews r ON r.course_id = c.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             WHERE c.created_by = ? GROUP BY c.id ORDER BY c.created_at DESC",
            [$instructorId]
        )->fetchAll();

        $this->render('instructor/courses/index', compact('courses'), 'dashboard');
    }

    public function createCourse(): void
    {
        $categories = \App\Services\CategoryService::getFlatTreeList();
        $this->render('instructor/courses/create', compact('categories'), 'dashboard');
    }

    public function storeCourse(): void
    {
        $instructorId = auth()['id'];
        $price = (float)$this->request->input('price', 0);
        $title = trim($this->request->input('title', ''));
        $categoryId = (int)$this->request->input('category_id');

        $reqLines = array_filter(array_map('trim', explode("\n", (string)$this->request->input('requirements', ''))));
        $outLines = array_filter(array_map('trim', explode("\n", (string)$this->request->input('what_you_learn', ''))));

        $data = [
            'title'                => $title,
            'short_description'   => $this->request->input('short_description'),
            'description'         => $this->request->input('description'),
            'category_id'         => $categoryId,
            'level'               => $this->request->input('level', 'beginner'),
            'price'               => $price,
            'discount_price'      => $this->request->input('discount_price') ? (float)$this->request->input('discount_price') : null,
            'is_free'             => $price <= 0 ? 1 : 0,
            'is_published'        => 0, // Draft by default until reviewed/published
            'duration_hours'      => (float)$this->request->input('duration_hours', 0),
            'requirements'        => json_encode(array_values($reqLines)),
            'learning_outcomes'   => json_encode(array_values($outLines)),
            'preview_video_url'   => $this->request->input('preview_video_url', ''),
            'created_by'          => $instructorId,
            'slug'                => $this->generateSlug($title),
            'certificate_included'=> 1,
            'passing_score'       => 70,
            'created_at'          => date('Y-m-d H:i:s'),
        ];

        // Handle thumbnail upload
        if (!empty($_FILES['thumbnail']['name'])) {
            $data['thumbnail'] = $this->uploadFile('thumbnail', 'courses');
        }

        $courseId = $this->db()->insert('courses', $data);

        // Also insert into course_instructors
        $this->db()->insert('course_instructors', [
            'course_id' => $courseId,
            'user_id'   => $instructorId
        ]);

        // Insert primary category into course_categories
        if ($categoryId > 0) {
            $this->db()->insert('course_categories', [
                'course_id' => $courseId,
                'category_id' => $categoryId,
                'is_primary' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->flash('success', 'Course created! Now build your curriculum sections and video lessons.');
        $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
    }

    public function editCourse(\App\Core\Request $request, int $id): void
    {
        if (!$this->canManageCourse($id)) {
            $this->abort(403);
            return;
        }

        $course = $this->db()->fetchOne("SELECT * FROM courses WHERE id = ?", [$id]);
        if (!$course) {
            $this->abort(404);
            return;
        }

        $categories = \App\Services\CategoryService::getFlatTreeList();
        $this->render('instructor/courses/edit', compact('course', 'categories'), 'dashboard');
    }

    public function updateCourse(\App\Core\Request $request, int $id): void
    {
        if (!$this->canManageCourse($id)) {
            $this->abort(403);
            return;
        }

        $price = (float)$this->request->input('price', 0);
        $reqLines = array_filter(array_map('trim', explode("\n", (string)$this->request->input('requirements', ''))));
        $outLines = array_filter(array_map('trim', explode("\n", (string)$this->request->input('what_you_learn', ''))));

        $data = [
            'title'             => $this->request->input('title'),
            'short_description' => $this->request->input('short_description'),
            'description'       => $this->request->input('description'),
            'category_id'       => (int)$this->request->input('category_id'),
            'level'             => $this->request->input('level', 'beginner'),
            'price'             => $price,
            'discount_price'    => $this->request->input('discount_price') !== '' && $this->request->input('discount_price') !== null ? (float)$this->request->input('discount_price') : null,
            'is_free'           => $price <= 0 ? 1 : 0,
            // Publishing status is controlled exclusively by CourseApprovalService
            // (submit-for-review -> approval -> publish), never by direct POST input.
            'duration_hours'    => (float)$this->request->input('duration_hours', 0),
            'requirements'      => json_encode(array_values($reqLines)),
            'learning_outcomes' => json_encode(array_values($outLines)),
            'preview_video_url' => $this->request->input('preview_video_url', ''),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        if (!empty($_FILES['thumbnail']['name'])) {
            $data['thumbnail'] = $this->uploadFile('thumbnail', 'courses');
        }

        $this->db()->update('courses', $data, ['id' => $id]);

        // Sync primary category in course_categories pivot
        if (!empty($data['category_id'])) {
            $this->db()->query("DELETE FROM course_categories WHERE course_id = ? AND is_primary = 1", [$id]);
            $this->db()->insert('course_categories', [
                'course_id'   => $id,
                'category_id' => $data['category_id'],
                'is_primary'  => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        $this->flash('success', 'Course updated successfully.');
        $this->redirect('/instructor/courses/' . $id . '/edit');
    }

    /**
     * Submit a draft (or changes-requested) course into the approval queue.
     */
    public function submitForReview(\App\Core\Request $request, int $id): void
    {
        if (!$this->canManageCourse($id)) {
            $this->abort(403);
            return;
        }

        try {
            (new \App\Services\CourseApprovalService())->submitForReview($id, (int)auth_id());
            $this->flash('success', 'Course submitted for review.');
        } catch (\Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }

        $this->redirect('/instructor/courses/' . $id . '/curriculum');
    }

    /**
     * Check if current user has permission to manage a course (owner instructor or admin/super_admin)
     */
    private function canManageCourse(int $courseId): bool
    {
        $user = auth();
        if (!$user) {
            return false;
        }
        if (in_array(auth_role(), ['admin', 'super_admin'], true)) {
            return true;
        }
        $course = $this->db()->fetchOne("SELECT id FROM courses WHERE id = ? AND created_by = ?", [$courseId, $user['id']]);
        return (bool)$course;
    }

    public function curriculum(\App\Core\Request $request, int $id): void
    {
        if (!$this->canManageCourse($id)) {
            $this->flash('error', 'You do not have permission to manage this course curriculum.');
            $this->redirect('/instructor/courses');
            return;
        }

        $course = $this->db()->fetchOne("SELECT * FROM courses WHERE id = ?", [$id]);
        if (!$course) {
            $this->abort(404);
            return;
        }

        $modules = $this->db()->query(
            "SELECT m.* FROM modules m WHERE m.course_id = ? ORDER BY m.sort_order ASC, m.id ASC",
            [$id]
        )->fetchAll();

        foreach ($modules as &$m) {
            $m['lessons'] = $this->db()->query(
                "SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC, id ASC",
                [$m['id']]
            )->fetchAll();
        }
        unset($m);

        $providers = \App\Services\VideoService::getProviders();

        $approvalHistory = $this->db()->query(
            "SELECT h.*, u.name as performed_by_name
             FROM course_approval_history h
             JOIN users u ON h.performed_by = u.id
             WHERE h.course_id = ?
             ORDER BY h.created_at DESC",
            [$id]
        )->fetchAll();

        $this->render('instructor/courses/curriculum', compact('course', 'modules', 'providers', 'approvalHistory'), 'dashboard');
    }

    public function storeModule(\App\Core\Request $request, int $courseId): void
    {
        if (!$this->canManageCourse($courseId)) {
            $this->abort(403);
            return;
        }

        $title = trim($this->request->input('title', ''));
        if ($title === '') {
            $this->flash('error', 'Module title is required.');
            $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
            return;
        }

        $maxSort = (int)($this->db()->fetchOne("SELECT MAX(sort_order) mx FROM modules WHERE course_id = ?", [$courseId])['mx'] ?? 0);

        $this->db()->insert('modules', [
            'course_id'   => $courseId,
            'title'       => $title,
            'description' => $this->request->input('description', ''),
            'sort_order'  => $maxSort + 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Module added successfully.');
        $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
    }

    public function deleteModule(\App\Core\Request $request, int $moduleId): void
    {
        $module = $this->db()->fetchOne("SELECT * FROM modules WHERE id = ?", [$moduleId]);
        if (!$module || !$this->canManageCourse((int)$module['course_id'])) {
            $this->abort(403);
            return;
        }

        $this->db()->query("DELETE FROM modules WHERE id = ?", [$moduleId]);
        $this->flash('success', 'Module and its lessons removed.');
        $this->redirect('/instructor/courses/' . $module['course_id'] . '/curriculum');
    }

    public function storeLesson(): void
    {
        $moduleId = (int)$this->request->input('module_id');
        $module   = $this->db()->fetchOne("SELECT * FROM modules WHERE id = ?", [$moduleId]);

        if (!$module || !$this->canManageCourse((int)$module['course_id'])) {
            $this->abort(403);
            return;
        }

        $courseId    = (int)$module['course_id'];
        $title       = trim($this->request->input('title', ''));
        $lessonType  = $this->request->input('content_type', 'video');
        $videoUrl    = trim($this->request->input('video_url', ''));
        $providerReq = $this->request->input('video_provider', 'auto');
        $content     = $this->request->input('text_content', '');

        // Auto-detect or sanitize provider
        $provider = ($providerReq === 'auto' || empty($providerReq))
            ? \App\Services\VideoService::detectProvider($videoUrl)
            : $providerReq;

        $slugBase = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug     = trim($slugBase, '-') . '-' . $courseId . '-' . $moduleId . '-' . time();

        $maxSort = (int)($this->db()->fetchOne("SELECT MAX(sort_order) mx FROM lessons WHERE module_id = ?", [$moduleId])['mx'] ?? 0);

        $data = [
            'module_id'        => $moduleId,
            'course_id'        => $courseId,
            'title'            => $title,
            'slug'             => substr($slug, 0, 200),
            'lesson_type'      => in_array($lessonType, ['video', 'pdf', 'text', 'audio', 'presentation']) ? $lessonType : 'video',
            'content'          => $content,
            'video_provider'   => $provider,
            'video_url'        => $videoUrl,
            'duration_minutes' => max(1, (int)$this->request->input('duration_minutes', 10)),
            'is_free_preview'  => $this->request->input('is_free_preview') ? 1 : 0,
            'is_published'     => 1,
            'sort_order'       => $maxSort + 1,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        // Handle PDF upload if provided
        if ($lessonType === 'pdf' && !empty($_FILES['pdf_file']['name'])) {
            $data['pdf_path'] = $this->uploadFile('pdf_file', 'lessons/pdfs');
        }

        $this->db()->insert('lessons', $data);

        $this->flash('success', 'Lesson "' . htmlspecialchars($title) . '" created successfully with ' . ucfirst($provider) . ' video player.');
        $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
    }

    public function editLesson(\App\Core\Request $request, int $lessonId): void
    {
        $lesson = $this->db()->fetchOne("SELECT * FROM lessons WHERE id = ?", [$lessonId]);
        if (!$lesson || !$this->canManageCourse((int)$lesson['course_id'])) {
            $this->abort(403);
            return;
        }

        $course  = $this->db()->fetchOne("SELECT * FROM courses WHERE id = ?", [$lesson['course_id']]);
        $module  = $this->db()->fetchOne("SELECT * FROM modules WHERE id = ?", [$lesson['module_id']]);
        $modules = $this->db()->query("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order ASC", [$lesson['course_id']])->fetchAll();
        $providers = \App\Services\VideoService::getProviders();

        $this->render('instructor/lessons/edit', compact('lesson', 'course', 'module', 'modules', 'providers'), 'dashboard');
    }

    public function updateLesson(\App\Core\Request $request, int $lessonId): void
    {
        $lesson = $this->db()->fetchOne("SELECT * FROM lessons WHERE id = ?", [$lessonId]);
        if (!$lesson || !$this->canManageCourse((int)$lesson['course_id'])) {
            $this->abort(403);
            return;
        }

        $title       = trim($this->request->input('title', $lesson['title']));
        $lessonType  = $this->request->input('content_type', $lesson['lesson_type']);
        $videoUrl    = trim($this->request->input('video_url', ''));
        $providerReq = $this->request->input('video_provider', 'auto');
        $content     = $this->request->input('text_content', '');
        $newModuleId = (int)$this->request->input('module_id', $lesson['module_id']);

        $provider = ($providerReq === 'auto' || empty($providerReq))
            ? \App\Services\VideoService::detectProvider($videoUrl)
            : $providerReq;

        $data = [
            'module_id'        => $newModuleId ?: $lesson['module_id'],
            'title'            => $title,
            'lesson_type'      => in_array($lessonType, ['video', 'pdf', 'text', 'audio', 'presentation']) ? $lessonType : 'video',
            'content'          => $content,
            'video_provider'   => $provider,
            'video_url'        => $videoUrl,
            'duration_minutes' => max(1, (int)$this->request->input('duration_minutes', 10)),
            'is_free_preview'  => $this->request->input('is_free_preview') ? 1 : 0,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        if ($lessonType === 'pdf' && !empty($_FILES['pdf_file']['name'])) {
            $data['pdf_path'] = $this->uploadFile('pdf_file', 'lessons/pdfs');
        }

        $this->db()->update('lessons', $data, ['id' => $lessonId]);

        $this->flash('success', 'Lesson updated successfully.');
        $this->redirect('/instructor/courses/' . $lesson['course_id'] . '/curriculum');
    }

    public function deleteLesson(\App\Core\Request $request, int $lessonId): void
    {
        $lesson = $this->db()->fetchOne("SELECT * FROM lessons WHERE id = ?", [$lessonId]);
        if (!$lesson || !$this->canManageCourse((int)$lesson['course_id'])) {
            $this->abort(403);
            return;
        }

        $courseId = $lesson['course_id'];
        $this->db()->query("DELETE FROM lessons WHERE id = ?", [$lessonId]);
        $this->flash('success', 'Lesson removed.');
        $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
    }

    public function students(): void
    {
        $instructorId = auth()['id'];
        $students = $this->db()->query(
            "SELECT e.*, u.name student_name, u.email student_email, c.title course_title,
                    ROUND(COUNT(lp.id) * 100.0 / NULLIF((SELECT COUNT(*) FROM lessons l2 JOIN modules m2 ON l2.module_id = m2.id WHERE m2.course_id = c.id), 0), 0) progress_percentage
             FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id
             LEFT JOIN lesson_progress lp ON lp.enrollment_id = e.id AND lp.is_completed = 1
             WHERE c.created_by = ? GROUP BY e.id ORDER BY e.created_at DESC",
            [$instructorId]
        )->fetchAll();

        $this->render('instructor/students', compact('students'), 'dashboard');
    }

    public function reviews(): void
    {
        $instructorId = auth()['id'];
        $isAdmin = in_array(auth_role(), ['admin', 'super_admin'], true);

        if ($isAdmin) {
            $reviews = $this->db()->query(
                "SELECT r.*, u.name student_name, u.email student_email, c.title course_title, c.slug course_slug
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 JOIN courses c ON r.course_id = c.id
                 ORDER BY r.created_at DESC"
            )->fetchAll();
        } else {
            $reviews = $this->db()->query(
                "SELECT r.*, u.name student_name, u.email student_email, c.title course_title, c.slug course_slug
                 FROM reviews r
                 JOIN users u ON r.user_id = u.id
                 JOIN courses c ON r.course_id = c.id
                 WHERE c.created_by = ?
                 ORDER BY r.created_at DESC",
                [$instructorId]
            )->fetchAll();
        }

        $this->render('instructor/reviews', compact('reviews'), 'dashboard');
    }

    public function quizzes(): void
    {
        $instructorId = auth()['id'];
        $isAdmin = in_array(auth_role(), ['admin', 'super_admin'], true);

        if ($isAdmin) {
            $quizzes = $this->db()->query(
                "SELECT q.*, c.title course_title, c.slug course_slug, m.title module_title,
                        COUNT(qq.id) question_count, SUM(qq.points) total_points
                 FROM quizzes q
                 JOIN courses c ON q.course_id = c.id
                 LEFT JOIN modules m ON q.module_id = m.id
                 LEFT JOIN quiz_questions qq ON qq.quiz_id = q.id
                 GROUP BY q.id ORDER BY q.created_at DESC"
            )->fetchAll();
        } else {
            $quizzes = $this->db()->query(
                "SELECT q.*, c.title course_title, c.slug course_slug, m.title module_title,
                        COUNT(qq.id) question_count, SUM(qq.points) total_points
                 FROM quizzes q
                 JOIN courses c ON q.course_id = c.id
                 LEFT JOIN modules m ON q.module_id = m.id
                 LEFT JOIN quiz_questions qq ON qq.quiz_id = q.id
                 WHERE c.created_by = ?
                 GROUP BY q.id ORDER BY q.created_at DESC",
                [$instructorId]
            )->fetchAll();
        }

        $courses = $isAdmin
            ? $this->db()->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll()
            : $this->db()->query("SELECT id, title FROM courses WHERE created_by = ? ORDER BY title ASC", [$instructorId])->fetchAll();

        $this->render('instructor/quizzes/index', compact('quizzes', 'courses'), 'dashboard');
    }

    public function storeQuiz(\App\Core\Request $request, int $courseId): void
    {
        if (!$this->canManageCourse($courseId)) {
            $this->abort(403);
            return;
        }

        $title = trim($this->request->input('title', ''));
        if ($title === '') {
            $this->flash('error', 'Quiz title is required.');
            $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
            return;
        }

        $moduleId = (int)$this->request->input('module_id') ?: null;

        $quizId = $this->db()->insert('quizzes', [
            'course_id'          => $courseId,
            'module_id'          => $moduleId,
            'title'              => $title,
            'description'        => $this->request->input('description', ''),
            'passing_score'      => max(1, min(100, (int)$this->request->input('passing_score', 70))),
            'time_limit_minutes' => max(1, (int)$this->request->input('time_limit_minutes', 20)),
            'max_attempts'       => max(1, (int)$this->request->input('max_attempts', 3)),
            'is_published'       => 1,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Assessment Quiz created! You can now add questions or generate them with AI.');
        $this->redirect('/instructor/quizzes/' . $quizId . '/edit');
    }

    public function editQuiz(\App\Core\Request $request, int $quizId): void
    {
        $quiz = $this->db()->fetchOne(
            "SELECT q.*, c.title course_title, c.slug course_slug, c.id course_id, m.title module_title
             FROM quizzes q
             JOIN courses c ON q.course_id = c.id
             LEFT JOIN modules m ON q.module_id = m.id
             WHERE q.id = ?",
            [$quizId]
        );

        if (!$quiz || !$this->canManageCourse((int)$quiz['course_id'])) {
            $this->abort(403);
            return;
        }

        $questions = $this->db()->query(
            "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY sort_order ASC, id ASC",
            [$quizId]
        )->fetchAll();

        foreach ($questions as &$q) {
            $q['options'] = $this->db()->query(
                "SELECT * FROM quiz_options WHERE question_id = ? ORDER BY id ASC",
                [$q['id']]
            )->fetchAll();
        }
        unset($q);

        $this->render('instructor/quizzes/edit', compact('quiz', 'questions'), 'dashboard');
    }

    public function updateQuiz(\App\Core\Request $request, int $quizId): void
    {
        $quiz = $this->db()->fetchOne("SELECT course_id FROM quizzes WHERE id = ?", [$quizId]);
        if (!$quiz || !$this->canManageCourse((int)$quiz['course_id'])) {
            $this->abort(403);
            return;
        }

        $this->db()->update('quizzes', [
            'title'              => $this->request->input('title'),
            'passing_score'      => max(1, min(100, (int)$this->request->input('passing_score', 70))),
            'time_limit_minutes' => max(1, (int)$this->request->input('time_limit_minutes', 20)),
            'max_attempts'       => max(1, (int)$this->request->input('max_attempts', 3)),
            'updated_at'         => date('Y-m-d H:i:s'),
        ], ['id' => $quizId]);

        $this->flash('success', 'Quiz settings updated.');
        $this->redirect('/instructor/quizzes/' . $quizId . '/edit');
    }

    public function deleteQuiz(\App\Core\Request $request, int $quizId): void
    {
        $quiz = $this->db()->fetchOne("SELECT course_id FROM quizzes WHERE id = ?", [$quizId]);
        if (!$quiz || !$this->canManageCourse((int)$quiz['course_id'])) {
            $this->abort(403);
            return;
        }

        $courseId = $quiz['course_id'];
        $this->db()->query("DELETE FROM quizzes WHERE id = ?", [$quizId]);
        $this->flash('success', 'Quiz removed successfully.');
        $this->redirect('/instructor/courses/' . $courseId . '/curriculum');
    }

    /**
     * AI Quiz Generation Endpoint
     */
    public function generateAiQuestions(\App\Core\Request $request, int $quizId): void
    {
        $quiz = $this->db()->fetchOne(
            "SELECT q.*, c.title course_title, m.title module_title
             FROM quizzes q
             JOIN courses c ON q.course_id = c.id
             LEFT JOIN modules m ON q.module_id = m.id
             WHERE q.id = ?",
            [$quizId]
        );

        if (!$quiz || !$this->canManageCourse((int)$quiz['course_id'])) {
            $this->abort(403);
            return;
        }

        $topic      = trim($this->request->input('topic', $quiz['title'] . ' — ' . ($quiz['module_title'] ?? $quiz['course_title'])));
        $count      = max(1, min(15, (int)$this->request->input('count', 5)));
        $difficulty = $this->request->input('difficulty', 'intermediate');
        $notes      = trim($this->request->input('notes', ''));

        // Generate questions using AI Quiz Service
        $questions = \App\Services\AiQuizService::generate($topic, $count, $difficulty, $notes);

        if (empty($questions)) {
            $this->flash('error', 'Could not generate questions for this topic. Please try with different keywords.');
            $this->redirect('/instructor/quizzes/' . $quizId . '/edit');
            return;
        }

        // Insert into database
        $inserted = \App\Services\AiQuizService::insertQuestionsIntoQuiz($quizId, $questions);

        $this->flash('success', "✨ AI successfully generated and added {$inserted} questions to this quiz!");
        $this->redirect('/instructor/quizzes/' . $quizId . '/edit');
    }

    public function storeQuestion(\App\Core\Request $request, int $quizId): void
    {
        $quiz = $this->db()->fetchOne("SELECT course_id FROM quizzes WHERE id = ?", [$quizId]);
        if (!$quiz || !$this->canManageCourse((int)$quiz['course_id'])) {
            $this->abort(403);
            return;
        }

        $type        = $this->request->input('question_type', 'single_choice');
        $options     = $this->request->input('options', []);
        $correct     = (int)$this->request->input('correct_option', 0);
        $explanation = trim($this->request->input('explanation', ''));
        $maxSort     = (int)($this->db()->fetchOne("SELECT MAX(sort_order) mx FROM quiz_questions WHERE quiz_id = ?", [$quizId])['mx'] ?? 0);

        $qId = $this->db()->insert('quiz_questions', [
            'quiz_id'       => $quizId,
            'question_text' => $this->request->input('question_text'),
            'question_type' => $type,
            'points'        => max(1, (int)$this->request->input('points', 10)),
            'explanation'   => $explanation,
            'sort_order'    => $maxSort + 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($type === 'true_false') {
            $tfCorrect = $this->request->input('tf_correct', 'True');
            $this->db()->insert('quiz_options', ['question_id' => $qId, 'option_text' => 'True',  'is_correct' => $tfCorrect === 'True' ? 1 : 0]);
            $this->db()->insert('quiz_options', ['question_id' => $qId, 'option_text' => 'False', 'is_correct' => $tfCorrect === 'False' ? 1 : 0]);
        } elseif ($type !== 'fill_blank') {
            foreach ($options as $i => $opt) {
                if (trim($opt) === '') continue;
                $this->db()->insert('quiz_options', [
                    'question_id' => $qId,
                    'option_text' => trim($opt),
                    'is_correct'  => ($i === $correct) ? 1 : 0,
                ]);
            }
        } else {
            $this->db()->insert('quiz_options', [
                'question_id' => $qId,
                'option_text' => trim((string)$this->request->input('fill_blank_answer')),
                'is_correct'  => 1,
            ]);
        }

        $this->flash('success', 'Question added.');
        $this->redirect('/instructor/quizzes/' . $quizId . '/edit');
    }

    public function deleteQuestion(\App\Core\Request $request, int $questionId): void
    {
        $q = $this->db()->fetchOne(
            "SELECT qq.*, qz.course_id FROM quiz_questions qq JOIN quizzes qz ON qq.quiz_id = qz.id WHERE qq.id = ?",
            [$questionId]
        );
        if (!$q || !$this->canManageCourse((int)$q['course_id'])) {
            $this->abort(403);
            return;
        }
        $this->db()->query("DELETE FROM quiz_questions WHERE id = ?", [$questionId]);
        $this->flash('success', 'Question deleted.');
        $this->redirect('/instructor/quizzes/' . $q['quiz_id'] . '/edit');
    }

    // ── Helpers ────────────────────────────────────────────────────
    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $orig = $slug;
        $i    = 1;
        while ($this->db()->fetchOne("SELECT id FROM courses WHERE slug = ?", [$slug])) {
            $slug = $orig . '-' . $i++;
        }
        return $slug;
    }

    private function uploadFile(string $field, string $folder): string
    {
        $file   = $_FILES[$field];
        $ext    = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name   = uniqid() . '.' . $ext;
        $target = dirname(__DIR__, 2) . '/public/uploads/' . $folder . '/';
        if (!is_dir($target)) mkdir($target, 0755, true);
        move_uploaded_file($file['tmp_name'], $target . $name);
        return $folder . '/' . $name;
    }
}
