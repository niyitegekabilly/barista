<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Course;
use App\Models\LessonNote;
use App\Models\LessonDiscussion;
use App\Models\LessonResource;
use App\Models\Notification;

class ClassroomService {

    /**
     * Load full 360° classroom payload with access gating, curriculum, notes, resources, and Q&A.
     */
    public static function getClassroomData(int $userId, string $courseSlug, int $lessonId = 0): array {
        $course = Database::fetchOne("SELECT * FROM courses WHERE slug = :s LIMIT 1", ['s' => trim($courseSlug)]);
        if (!$course) {
            return ['success' => false, 'code' => 404, 'message' => 'Course not found.'];
        }

        $courseId = (int)$course['id'];

        // 1. Verify Access: Direct Enrollment OR Active Membership OR Admin/Instructor Role
        $enrollment = Database::fetchOne(
            "SELECT * FROM enrollments WHERE course_id = :cid AND user_id = :uid LIMIT 1",
            ['cid' => $courseId, 'uid' => $userId]
        );

        $hasMembershipAccess = MembershipService::canUserAccessCourse($userId, $courseId);
        $userRole = auth_role();
        $isPrivileged = in_array($userRole, ['admin', 'super_admin', 'instructor'], true);

        if (!$enrollment) {
            if ($hasMembershipAccess || $isPrivileged) {
                // Auto-provision active enrollment record
                $enrollId = Database::insert('enrollments', [
                    'user_id'          => $userId,
                    'course_id'        => $courseId,
                    'status'           => 'active',
                    'progress_percent' => 0,
                    'enrolled_at'      => date('Y-m-d H:i:s'),
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s')
                ]);
                $enrollment = Database::fetchOne("SELECT * FROM enrollments WHERE id = :id", ['id' => $enrollId]);
            } else {
                return [
                    'success' => false,
                    'code' => 403,
                    'message' => 'Please enroll in this course or activate a Beyond Barista Membership to access the classroom.',
                    'course' => $course
                ];
            }
        }

        $enrollmentId = (int)$enrollment['id'];

        // 2. Load Modules & Lessons
        $modules = Database::fetchAll(
            "SELECT * FROM modules WHERE course_id = :cid ORDER BY sort_order ASC",
            ['cid' => $courseId]
        );

        $completedLessonIds = array_column(
            Database::fetchAll(
                "SELECT DISTINCT lesson_id FROM lesson_progress WHERE enrollment_id = :eid AND is_completed = 1",
                ['eid' => $enrollmentId]
            ),
            'lesson_id'
        );

        $allLessons = [];
        foreach ($modules as &$module) {
            $module['lessons'] = Database::fetchAll(
                "SELECT l.*, IF(lp.is_completed = 1, 1, 0) as is_completed, lp.last_position_seconds
                 FROM lessons l
                 LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.enrollment_id = :eid
                 WHERE l.module_id = :mid
                 ORDER BY l.sort_order ASC",
                ['eid' => $enrollmentId, 'mid' => $module['id']]
            );

            // Fetch attached quizzes if any
            $module['quizzes'] = Database::fetchAll(
                "SELECT q.*, (SELECT score_percentage FROM quiz_attempts WHERE quiz_id = q.id AND user_id = :uid ORDER BY score_percentage DESC LIMIT 1) as best_score
                 FROM quizzes q WHERE q.module_id = :mid",
                ['uid' => $userId, 'mid' => $module['id']]
            );

            foreach ($module['lessons'] as $les) {
                $allLessons[] = $les;
            }
        }

        // 3. Resolve Current Lesson
        if (!$lessonId) {
            // Pick last accessed lesson if stored
            if (!empty($enrollment['last_accessed_lesson_id'])) {
                $lessonId = (int)$enrollment['last_accessed_lesson_id'];
            } else {
                // Find first incomplete lesson
                foreach ($allLessons as $l) {
                    if (empty($l['is_completed'])) {
                        $lessonId = (int)$l['id'];
                        break;
                    }
                }
                // Fallback to first lesson
                if (!$lessonId && !empty($allLessons[0])) {
                    $lessonId = (int)$allLessons[0]['id'];
                }
            }
        }

        $currentLesson = null;
        if ($lessonId) {
            $currentLesson = Database::fetchOne("SELECT * FROM lessons WHERE id = :id LIMIT 1", ['id' => $lessonId]);
            if ($currentLesson) {
                // Update last accessed lesson on enrollment
                Database::update('enrollments', ['last_accessed_lesson_id' => $lessonId, 'last_activity_date' => date('Y-m-d')], ['id' => $enrollmentId]);
            }
        }

        // 4. Calculate Navigation (Prev & Next)
        $currentIndex = -1;
        if ($currentLesson) {
            foreach ($allLessons as $idx => $al) {
                if ((int)$al['id'] === (int)$currentLesson['id']) {
                    $currentIndex = $idx;
                    break;
                }
            }
        }
        $prevLesson = ($currentIndex > 0) ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = ($currentIndex >= 0 && isset($allLessons[$currentIndex + 1])) ? $allLessons[$currentIndex + 1] : null;

        // 5. Load Lesson Resources (Attachments), Student Notes, Discussions, Bookmarks, and Saved Progress
        $resources = [];
        $notes = [];
        $discussions = [];
        $bookmarks = [];
        $lessonProgress = null;

        if ($currentLesson) {
            $curLessonId = (int)$currentLesson['id'];

            $resources = Database::fetchAll(
                "SELECT * FROM lesson_resources WHERE lesson_id = :lid ORDER BY created_at ASC",
                ['lid' => $curLessonId]
            );

            $notes = Database::fetchAll(
                "SELECT * FROM lesson_notes WHERE user_id = :uid AND lesson_id = :lid ORDER BY timestamp_seconds ASC, created_at DESC",
                ['uid' => $userId, 'lid' => $curLessonId]
            );

            $discussions = LessonDiscussion::getThreadedForLesson($curLessonId);

            $bookmarks = Database::fetchAll(
                "SELECT * FROM lesson_bookmarks WHERE user_id = :uid AND lesson_id = :lid ORDER BY timestamp_seconds ASC",
                ['uid' => $userId, 'lid' => $curLessonId]
            );

            $lessonProgress = Database::fetchOne(
                "SELECT * FROM lesson_progress WHERE enrollment_id = :eid AND lesson_id = :lid LIMIT 1",
                ['eid' => $enrollmentId, 'lid' => $curLessonId]
            );
        }

        // 6. Overall Course Progress
        $totalLessons = count($allLessons);
        $completedCount = count(array_unique($completedLessonIds));
        $progressPct = ($totalLessons > 0) ? min(100, (int)round(($completedCount / $totalLessons) * 100)) : 0;
        $enrollment['progress_percent'] = $progressPct;

        return [
            'success'            => true,
            'course'             => $course,
            'enrollment'         => $enrollment,
            'modules'            => $modules,
            'all_lessons'        => $allLessons,
            'current_lesson'     => $currentLesson,
            'prev_lesson'        => $prevLesson,
            'next_lesson'        => $nextLesson,
            'completed_lesson_ids' => $completedLessonIds,
            'progress_percent'   => $progressPct,
            'resources'          => $resources,
            'notes'              => $notes,
            'discussions'        => $discussions,
            'bookmarks'          => $bookmarks,
            'lesson_progress'    => $lessonProgress
        ];
    }

    /**
     * Save real-time video progress heartbeat and mark completion.
     */
    public static function saveProgress(
        int $userId,
        int $enrollmentId,
        int $lessonId,
        int $positionSeconds = 0,
        int $timeSpentSeconds = 0,
        bool $markComplete = false,
        float $speed = 1.0
    ): array {
        $enrollment = Database::fetchOne("SELECT * FROM enrollments WHERE id = :id AND user_id = :uid", ['id' => $enrollmentId, 'uid' => $userId]);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Unauthorized enrollment.'];
        }

        $existing = Database::fetchOne(
            "SELECT * FROM lesson_progress WHERE enrollment_id = :eid AND lesson_id = :lid",
            ['eid' => $enrollmentId, 'lid' => $lessonId]
        );

        $now = date('Y-m-d H:i:s');
        $isCompletedVal = ($markComplete || ($existing && (int)$existing['is_completed'] === 1)) ? 1 : 0;
        $completedAtVal = ($isCompletedVal === 1) ? ($existing['completed_at'] ?? $now) : null;
        $accumulatedTime = ($existing ? (int)$existing['time_spent_seconds'] : 0) + $timeSpentSeconds;

        if ($existing) {
            Database::update('lesson_progress', [
                'is_completed'          => $isCompletedVal,
                'last_position_seconds' => $positionSeconds,
                'playback_speed'        => $speed,
                'time_spent_seconds'    => $accumulatedTime,
                'completed_at'          => $completedAtVal,
                'last_watched_at'       => $now,
                'updated_at'            => $now
            ], ['id' => $existing['id']]);
        } else {
            Database::insert('lesson_progress', [
                'enrollment_id'         => $enrollmentId,
                'user_id'               => $userId,
                'lesson_id'             => $lessonId,
                'is_completed'          => $isCompletedVal,
                'last_position_seconds' => $positionSeconds,
                'playback_speed'        => $speed,
                'time_spent_seconds'    => $accumulatedTime,
                'completed_at'          => $completedAtVal,
                'last_watched_at'       => $now,
                'created_at'            => $now,
                'updated_at'            => $now
            ]);
        }

        // Recalculate Course Progress Percentage
        $courseId = (int)$enrollment['course_id'];
        $totalLessons = (int)(Database::fetchValue("SELECT COUNT(*) FROM lessons WHERE course_id = :cid", ['cid' => $courseId]) ?: 1);
        $completedLessons = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT lesson_id) FROM lesson_progress WHERE enrollment_id = :eid AND is_completed = 1",
            ['eid' => $enrollmentId]
        ) ?: 0);

        $newProgressPct = min(100, (int)round(($completedLessons / $totalLessons) * 100));

        $updateData = [
            'progress_percent'        => $newProgressPct,
            'last_activity_date'      => date('Y-m-d'),
            'total_time_spent_seconds' => (int)$enrollment['total_time_spent_seconds'] + $timeSpentSeconds,
            'updated_at'              => $now
        ];

        // If 100% completed, mark course completed
        if ($newProgressPct >= 100 && empty($enrollment['completed_at'])) {
            $updateData['completed_at'] = $now;
            $updateData['status'] = 'completed';

            Notification::send(
                $userId,
                'Congratulations on Completing Your Course!',
                'You have completed 100% of the curriculum for ' . ($enrollment['course_title'] ?? 'your course') . '. Your official certificate is ready to view.',
                url('student/certificates')
            );
        }

        Database::update('enrollments', $updateData, ['id' => $enrollmentId]);

        return [
            'success'           => true,
            'is_completed'      => ($isCompletedVal === 1),
            'progress_percent'  => $newProgressPct,
            'completed_lessons' => $completedLessons,
            'total_lessons'     => $totalLessons
        ];
    }

    /**
     * Save private student note.
     */
    public static function saveNote(int $userId, int $courseId, int $lessonId, string $noteText, int $timestampSeconds = 0): array {
        $text = trim($noteText);
        if (empty($text)) {
            return ['success' => false, 'message' => 'Note text cannot be empty.'];
        }

        $noteId = Database::insert('lesson_notes', [
            'user_id'           => $userId,
            'course_id'         => $courseId,
            'lesson_id'         => $lessonId,
            'note_text'         => $text,
            'timestamp_seconds' => $timestampSeconds,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ]);

        return [
            'success'           => true,
            'note_id'           => $noteId,
            'timestamp_formatted' => LessonNote::formatTimestamp($timestampSeconds),
            'message'           => 'Note saved successfully.'
        ];
    }

    /**
     * Delete student note.
     */
    public static function deleteNote(int $userId, int $noteId): bool {
        return (bool)Database::query("DELETE FROM lesson_notes WHERE id = :id AND user_id = :uid", ['id' => $noteId, 'uid' => $userId]);
    }

    /**
     * Post a question or reply to community discussion.
     */
    public static function postDiscussion(int $userId, int $courseId, int $lessonId, string $question, ?int $parentId = null): array {
        $text = trim($question);
        if (empty($text)) {
            return ['success' => false, 'message' => 'Discussion message cannot be empty.'];
        }

        $userRole = auth_role();
        $isInstructor = in_array($userRole, ['instructor', 'admin', 'super_admin'], true);

        $discId = Database::insert('lesson_discussions', [
            'lesson_id'           => $lessonId,
            'course_id'           => $courseId,
            'user_id'             => $userId,
            'parent_id'           => $parentId,
            'question'            => $text,
            'is_answered'         => $isInstructor ? 1 : 0,
            'is_instructor_reply' => $isInstructor ? 1 : 0,
            'upvotes'             => 0,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s')
        ]);

        if ($parentId && $isInstructor) {
            Database::update('lesson_discussions', ['is_answered' => 1], ['id' => $parentId]);
        }

        return [
            'success' => true,
            'discussion_id' => $discId,
            'message' => 'Message posted to lesson discussion.'
        ];
    }

    /**
     * Toggle saved bookmark.
     */
    public static function toggleBookmark(int $userId, int $lessonId, string $title, int $timestampSeconds = 0): array {
        $existing = Database::fetchOne(
            "SELECT id FROM lesson_bookmarks WHERE user_id = :uid AND lesson_id = :lid AND timestamp_seconds = :ts",
            ['uid' => $userId, 'lid' => $lessonId, 'ts' => $timestampSeconds]
        );

        if ($existing) {
            Database::query("DELETE FROM lesson_bookmarks WHERE id = :id", ['id' => $existing['id']]);
            return ['success' => true, 'action' => 'removed', 'message' => 'Bookmark removed.'];
        } else {
            $bmId = Database::insert('lesson_bookmarks', [
                'user_id'           => $userId,
                'lesson_id'         => $lessonId,
                'title'             => trim($title) ?: ('Bookmark at ' . LessonNote::formatTimestamp($timestampSeconds)),
                'timestamp_seconds' => $timestampSeconds,
                'created_at'        => date('Y-m-d H:i:s')
            ]);
            return ['success' => true, 'action' => 'added', 'id' => $bmId, 'message' => 'Moment bookmarked!'];
        }
    }
}
