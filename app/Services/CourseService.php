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
}
