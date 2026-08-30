<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Core\Database;

class CourseService {
    public static function enroll(int $userId, int $courseId): ?array {
        $existing = Enrollment::getUserEnrollment($userId, $courseId);
        if ($existing) {
            return $existing;
        }

        $course = Course::find($courseId);
        if (!$course || !$course['is_published']) {
            return null;
        }

        // Find first lesson
        $firstLesson = Database::fetchOne("SELECT id FROM lessons WHERE course_id = :cid AND is_published = 1 ORDER BY sort_order ASC LIMIT 1", ['cid' => $courseId]);

        $enrollmentId = Enrollment::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'status' => 'active',
            'progress_percent' => 0,
            'last_accessed_lesson_id' => $firstLesson['id'] ?? null,
            'enrolled_at' => date('Y-m-d H:i:s')
        ]);

        Notification::send(
            $userId,
            'Course Enrolled!',
            "You have successfully enrolled in {$course['title']}. Happy learning!",
            url("student/classroom/{$course['slug']}")
        );

        AuditLog::log('course_enrolled', 'course', $courseId, ['user_id' => $userId]);

        return Enrollment::find($enrollmentId);
    }

    public static function completeLesson(int $userId, int $courseId, int $lessonId): array {
        $enrollment = Enrollment::getUserEnrollment($userId, $courseId);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Not enrolled in this course.'];
        }

        // Record or update lesson progress
        $progress = Database::fetchOne("SELECT * FROM lesson_progress WHERE enrollment_id = :eid AND lesson_id = :lid LIMIT 1", [
            'eid' => $enrollment['id'],
            'lid' => $lessonId
        ]);

        if ($progress) {
            Database::update('lesson_progress', [
                'is_completed' => 1,
                'completed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], "id = :id", ['id' => $progress['id']]);
        } else {
            Database::insert('lesson_progress', [
                'enrollment_id' => $enrollment['id'],
                'user_id' => $userId,
                'lesson_id' => $lessonId,
                'is_completed' => 1,
                'completed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Update enrollment's last accessed lesson
        Enrollment::update($enrollment['id'], [
            'last_accessed_lesson_id' => $lessonId
        ]);

        // Recalculate progress percent and completion status
        $progressStats = Enrollment::updateProgress($enrollment['id']);

        // Check if course has a quiz or if 100% lessons qualifies for auto-completion
        $hasQuizzes = (int) Database::fetchValue("SELECT COUNT(*) FROM quizzes WHERE course_id = :cid AND is_published = 1", ['cid' => $courseId]);

        if ($progressStats['completed'] && $hasQuizzes === 0) {
            // Auto issue certificate if course has no mandatory quiz
            CertificateService::generate((int)$enrollment['id']);
        }

        return [
            'success' => true,
            'message' => 'Progress updated.',
            'progress' => $progressStats['progress'],
            'is_completed' => $progressStats['completed']
        ];
    }

    /**
     * KPI cards for the admin Courses & Approval dashboard.
     */
    public static function getKpiStats(): array {
        $counts = Database::fetchAll(
            "SELECT status, COUNT(*) as cnt FROM courses GROUP BY status"
        );
        $byStatus = array_column($counts, 'cnt', 'status');

        return [
            'total' => (int)array_sum($byStatus),
            'published' => (int)($byStatus['published'] ?? 0),
            'drafts' => (int)($byStatus['draft'] ?? 0),
            'pending_review' => (int)($byStatus['pending_review'] ?? 0) + (int)($byStatus['under_review'] ?? 0),
            'changes_requested' => (int)($byStatus['changes_requested'] ?? 0),
            'scheduled' => (int)($byStatus['scheduled'] ?? 0),
            'archived' => (int)($byStatus['archived'] ?? 0),
            'free' => (int)(Database::fetchValue("SELECT COUNT(*) FROM courses WHERE is_free = 1") ?: 0),
            'paid' => (int)(Database::fetchValue("SELECT COUNT(*) FROM courses WHERE is_free = 0") ?: 0),
        ];
    }

    /**
     * Advanced search/filter/sort/paginate for the admin course list.
     * Mirrors UserService::queryUsers() for consistency.
     */
    public static function queryCourses(array $filters = []): array {
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(5, min(100, (int)($filters['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(c.title LIKE :q1 OR u.name LIKE :q2)";
            $params['q1'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = "c.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['instructor_id'])) {
            $conditions[] = "c.created_by = :instructor_id";
            $params['instructor_id'] = (int)$filters['instructor_id'];
        }

        if (isset($filters['is_free']) && $filters['is_free'] !== '') {
            $conditions[] = "c.is_free = :is_free";
            $params['is_free'] = (int)$filters['is_free'];
        }

        $whereSql = implode(' AND ', $conditions);

        $allowedSorts = [
            'title' => 'c.title',
            'status' => 'c.status',
            'created_at' => 'c.created_at',
            'updated_at' => 'c.updated_at',
            'enrollment_count' => 'enrollment_count',
        ];
        $sortBy = $allowedSorts[$filters['sort'] ?? 'updated_at'] ?? 'c.updated_at';
        $sortDir = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $countSql = "SELECT COUNT(DISTINCT c.id) as cnt
                     FROM courses c
                     JOIN users u ON c.created_by = u.id
                     WHERE {$whereSql}";
        $total = (int)(Database::fetchValue($countSql, $params) ?: 0);

        $sql = "SELECT c.*, u.name as instructor_name, cat.name as category_name,
                       COUNT(DISTINCT e.id) as enrollment_count,
                       ROUND(AVG(r.rating), 1) as avg_rating,
                       ROUND(100 * SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) / NULLIF(COUNT(DISTINCT e.id), 0), 1) as completion_rate
                FROM courses c
                JOIN users u ON c.created_by = u.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN enrollments e ON e.course_id = c.id
                LEFT JOIN reviews r ON r.course_id = c.id AND r.is_approved = 1
                WHERE {$whereSql}
                GROUP BY c.id
                ORDER BY {$sortBy} {$sortDir}
                LIMIT {$perPage} OFFSET {$offset}";

        $courses = Database::fetchAll($sql, $params);

        return [
            'data' => $courses,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => max(1, (int)ceil($total / $perPage)),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total),
            ],
        ];
    }

    /**
     * Full detail payload for the admin course review/detail page.
     */
    public static function getCourseDetail(int $courseId): ?array {
        $course = Database::fetchOne(
            "SELECT c.*, u.name as instructor_name, u.email as instructor_email, cat.name as category_name
             FROM courses c
             JOIN users u ON c.created_by = u.id
             LEFT JOIN categories cat ON c.category_id = cat.id
             WHERE c.id = :id",
            ['id' => $courseId]
        );
        if (!$course) {
            return null;
        }

        $course['modules'] = Course::getCurriculum($courseId);

        $stats = Database::fetchOne(
            "SELECT COUNT(DISTINCT e.id) as enrollment_count,
                    ROUND(100 * SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) / NULLIF(COUNT(DISTINCT e.id), 0), 1) as completion_rate,
                    ROUND(AVG(e.progress_percent), 1) as avg_progress
             FROM enrollments e WHERE e.course_id = :cid",
            ['cid' => $courseId]
        );
        $course['enrollment_count'] = (int)($stats['enrollment_count'] ?? 0);
        $course['completion_rate'] = $stats['completion_rate'] ?? 0;
        $course['avg_progress'] = $stats['avg_progress'] ?? 0;

        $ratingStats = Database::fetchOne(
            "SELECT ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as review_count
             FROM reviews WHERE course_id = :cid AND is_approved = 1",
            ['cid' => $courseId]
        );
        $course['avg_rating'] = $ratingStats['avg_rating'] ?? null;
        $course['review_count'] = (int)($ratingStats['review_count'] ?? 0);

        $course['approval_history'] = Database::fetchAll(
            "SELECT h.*, u.name as performed_by_name
             FROM course_approval_history h
             JOIN users u ON h.performed_by = u.id
             WHERE h.course_id = :cid
             ORDER BY h.created_at DESC",
            ['cid' => $courseId]
        );

        return $course;
    }

    /**
     * Bulk lifecycle actions from the admin course list. Each course is run
     * through CourseApprovalService individually so permission checks, the
     * approval-history trail, and audit logging are never bypassed — this is
     * deliberately not a raw bulk UPDATE like UserService's simpler bulk
     * status flips, because course transitions must stay state-machine-safe.
     */
    public static function executeBulkAction(string $action, array $courseIds, int $actingUserId): array {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (empty($courseIds)) {
            return ['success' => false, 'message' => 'No courses selected.'];
        }

        $approval = new CourseApprovalService();
        $succeeded = 0;
        $errors = [];

        foreach ($courseIds as $id) {
            try {
                match ($action) {
                    'publish' => $approval->publish($id, $actingUserId),
                    'unpublish' => $approval->unpublish($id, $actingUserId),
                    'archive' => $approval->archive($id, $actingUserId),
                    default => throw new \RuntimeException("Unknown bulk action: {$action}"),
                };
                $succeeded++;
            } catch (\Throwable $e) {
                $errors[] = "#{$id}: " . $e->getMessage();
            }
        }

        $message = "{$succeeded} of " . count($courseIds) . " course(s) updated.";
        if (!empty($errors)) {
            $message .= ' Skipped: ' . implode('; ', $errors);
        }

        return ['success' => $succeeded > 0, 'message' => $message, 'succeeded' => $succeeded, 'errors' => $errors];
    }
}
