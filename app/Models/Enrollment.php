<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Enrollment extends Model {
    protected static string $table = 'enrollments';

    public static function getUserEnrollment(int $userId, int $courseId): ?array {
        return Database::fetchOne("SELECT * FROM enrollments WHERE user_id = :uid AND course_id = :cid LIMIT 1", [
            'uid' => $userId,
            'cid' => $courseId
        ]);
    }

    public static function getUserCourses(int $userId): array {
        $sql = "SELECT e.*, c.title, c.slug, c.thumbnail, c.duration_hours, c.level, cat.name as category_name,
                       (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as total_lessons,
                       (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.enrollment_id = e.id AND lp.is_completed = 1) as completed_lessons,
                       cert.certificate_number
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN certificates cert ON cert.enrollment_id = e.id
                WHERE e.user_id = :uid
                ORDER BY e.updated_at DESC";
        return Database::fetchAll($sql, ['uid' => $userId]);
    }

    public static function updateProgress(int $enrollmentId): array {
        $enrollment = self::find($enrollmentId);
        if (!$enrollment) {
            return ['progress' => 0, 'completed' => false];
        }

        $courseId = $enrollment['course_id'];
        $totalLessons = (int) Database::fetchValue("SELECT COUNT(*) FROM lessons WHERE course_id = :cid AND is_published = 1", ['cid' => $courseId]);
        $completedLessons = (int) Database::fetchValue("SELECT COUNT(*) FROM lesson_progress WHERE enrollment_id = :eid AND is_completed = 1", ['eid' => $enrollmentId]);

        $percent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
        if ($percent > 100) $percent = 100;

        $isCompleted = ($percent === 100);
        $data = [
            'progress_percent' => $percent,
            'status' => $isCompleted ? 'completed' : 'active',
            'completed_at' => $isCompleted ? ($enrollment['completed_at'] ?? date('Y-m-d H:i:s')) : null
        ];

        self::update($enrollmentId, $data);
        return ['progress' => $percent, 'completed' => $isCompleted];
    }
}
