<?php

namespace App\Controllers;

use App\Core\Controller;

class ClassroomController extends Controller
{
    public function show(\App\Core\Request $request, string $courseSlug, int $lessonId = 0): void
    {
        $userId = auth()['id'];

        $course = $this->db()->fetchOne("SELECT * FROM courses WHERE slug = ?", [$courseSlug]);
        if (!$course) {
            $this->abort(404);
            return;
        }

        // Verify enrollment
        $enrollment = $this->db()->fetchOne(
            "SELECT e.* FROM enrollments e
             WHERE e.course_id = ? AND e.user_id = ?",
            [$course['id'], $userId]
        );

        if (!$enrollment) {
            if (in_array(auth_role(), ['admin', 'super_admin', 'instructor'], true)) {
                // Auto-create or preview enrollment for instructor/admin
                $enrollId = $this->db()->insert('enrollments', [
                    'user_id'          => $userId,
                    'course_id'        => $course['id'],
                    'status'           => 'active',
                    'progress_percent' => 0,
                    'enrolled_at'      => date('Y-m-d H:i:s'),
                ]);
                $enrollment = $this->db()->fetchOne("SELECT * FROM enrollments WHERE id = ?", [$enrollId]);
            } else {
                $this->flash('error', 'Please enroll in this course to access the classroom.');
                $this->redirect('/courses/' . $courseSlug);
                return;
            }
        }

        // Load curriculum with progress
        $modules = $this->db()->query(
            "SELECT m.*, GROUP_CONCAT(l.id) lesson_ids FROM modules m
             LEFT JOIN lessons l ON l.module_id = m.id
             WHERE m.course_id = ? GROUP BY m.id ORDER BY m.sort_order",
            [$course['id']]
        )->fetchAll();

        // Enrich with lessons and completion status
        foreach ($modules as &$module) {
            $module['lessons'] = $this->db()->query(
                "SELECT l.*, IF(lp.is_completed = 1, 1, 0) is_completed
                 FROM lessons l
                 LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.enrollment_id = ?
                 WHERE l.module_id = ? ORDER BY l.sort_order",
                [$enrollment['id'], $module['id']]
            )->fetchAll();
        }

        // Determine active lesson
        if (!$lessonId) {
            // Find first incomplete lesson
            foreach ($modules as $m) {
                foreach ($m['lessons'] as $l) {
                    if (!$l['is_completed']) {
                        $lessonId = $l['id'];
                        break 2;
                    }
                }
            }
            // Fallback to first lesson
            if (!$lessonId && !empty($modules[0]['lessons'])) {
                $lessonId = $modules[0]['lessons'][0]['id'];
            }
        }

        $currentLesson = $this->db()->fetchOne("SELECT * FROM lessons WHERE id = ?", [$lessonId]);

        // Overall progress
        $progressPct = $this->db()->fetchOne(
            "SELECT ROUND(COUNT(lp.id) * 100.0 / NULLIF((SELECT COUNT(*) FROM lessons l2
              JOIN modules m2 ON l2.module_id = m2.id WHERE m2.course_id = ?), 0), 0) pct
             FROM lesson_progress lp WHERE lp.enrollment_id = ? AND lp.is_completed = 1",
            [$course['id'], $enrollment['id']]
        )['pct'] ?? 0;

        // Completed lesson IDs
        $completedLessonIds = array_column(
            $this->db()->query(
                "SELECT DISTINCT lesson_id FROM lesson_progress WHERE enrollment_id = ? AND is_completed = 1",
                [$enrollment['id']]
            )->fetchAll(),
            'lesson_id'
        );

        // Get all lessons in order
        $allLessons = [];
        foreach ($modules as $m) {
            foreach ($m['lessons'] as $l) {
                $allLessons[] = $l;
            }
        }

        // Find previous and next lessons
        $currentIndex = array_search($lessonId, array_column($allLessons, 'id'));
        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = isset($allLessons[$currentIndex + 1]) ? $allLessons[$currentIndex + 1] : null;

        // Add progress percent to enrollment
        $enrollment['progress_percent'] = $progressPct;

        $this->render('student/classroom', compact('course', 'modules', 'currentLesson', 'enrollment', 'progressPct', 'completedLessonIds', 'prevLesson', 'nextLesson'), 'dashboard');
    }

    /**
     * AJAX: Mark lesson as complete
     */
    public function completeLesson(): void
    {
        $userId   = auth()['id'];
        $lessonId = (int)$this->request->json('lesson_id');
        $enrollId = (int)$this->request->json('enrollment_id');

        // Verify ownership
        $enrollment = $this->db()->fetchOne("SELECT * FROM enrollments WHERE id = ? AND user_id = ?", [$enrollId, $userId]);
        if (!$enrollment) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        // Upsert progress record
        $this->db()->query(
            "INSERT INTO lesson_progress (enrollment_id, user_id, lesson_id, is_completed, completed_at)
             VALUES (:eid, :uid, :lid, 1, NOW())
             ON DUPLICATE KEY UPDATE is_completed=1, completed_at=NOW()",
            ['eid' => $enrollId, 'uid' => $userId, 'lid' => $lessonId]
        );

        // Recalculate progress
        $progressPct = $this->db()->fetchOne(
            "SELECT ROUND(COUNT(*) * 100.0 / NULLIF((SELECT COUNT(*) FROM lessons l2
              JOIN modules m2 ON l2.module_id = m2.id
              JOIN enrollments e2 ON e2.course_id = m2.course_id WHERE e2.id = ?), 0), 0) pct
             FROM lesson_progress WHERE enrollment_id = ? AND is_completed = 1",
            [$enrollId, $enrollId]
        )['pct'] ?? 0;

        // If 100% mark course completed
        if ($progressPct >= 100) {
            $this->db()->query(
                "UPDATE enrollments SET completed_at = NOW() WHERE id = ? AND completed_at IS NULL",
                [$enrollId]
            );
        }

        $this->json(['success' => true, 'progress' => (int)$progressPct]);
    }
}
