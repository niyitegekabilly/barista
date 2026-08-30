<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Core\Database;
use RuntimeException;

/**
 * Owns the course lifecycle state machine: every status change on `courses`
 * (draft -> pending_review -> under_review -> {approved|rejected|changes_requested}
 * -> {scheduled|published} -> unpublished -> archived) goes through here so that
 * permission checks, `course_approval_history`, `audit_logs`, and instructor
 * notifications are always applied together and never skipped.
 */
class CourseApprovalService
{
    public const STATUSES = [
        'draft', 'pending_review', 'under_review', 'changes_requested',
        'approved', 'scheduled', 'published', 'unpublished', 'archived', 'rejected',
    ];

    public function submitForReview(int $courseId, int $userId): array
    {
        $this->guardOwnerOrPermission($courseId, $userId, 'courses.submit_review');
        return $this->transition($courseId, $userId, ['draft', 'changes_requested'], 'pending_review', 'submitted', [
            'submitted_by' => $userId,
            'submitted_at' => date('Y-m-d H:i:s'),
            'reviewer_id' => null,
            'rejection_reason' => null,
        ]);
    }

    public function startReview(int $courseId, int $reviewerId): array
    {
        $this->guardPermission($reviewerId, 'courses.review');
        return $this->transition($courseId, $reviewerId, ['pending_review'], 'under_review', 'review_started', [
            'reviewer_id' => $reviewerId,
        ]);
    }

    public function approve(int $courseId, int $reviewerId, ?string $comment = null): array
    {
        $this->guardPermission($reviewerId, 'courses.review');
        $result = $this->transition($courseId, $reviewerId, ['under_review', 'pending_review'], 'approved', 'approved', [
            'reviewer_id' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => null,
        ], $comment);

        $this->notifyOwner($courseId, 'Course Approved', 'Your course "%s" has been approved and is ready to publish.');
        return $result;
    }

    public function reject(int $courseId, int $reviewerId, string $comment): array
    {
        if (trim($comment) === '') {
            throw new RuntimeException('A comment is required to reject a course.');
        }
        $this->guardPermission($reviewerId, 'courses.review');
        $result = $this->transition($courseId, $reviewerId, ['under_review', 'pending_review'], 'rejected', 'rejected', [
            'reviewer_id' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $comment,
        ], $comment);

        $this->notifyOwner($courseId, 'Course Rejected', 'Your course "%s" was rejected. Reason: ' . $comment);
        return $result;
    }

    public function requestChanges(int $courseId, int $reviewerId, string $comment): array
    {
        if (trim($comment) === '') {
            throw new RuntimeException('A comment is required to request changes.');
        }
        $this->guardPermission($reviewerId, 'courses.review');
        $result = $this->transition($courseId, $reviewerId, ['under_review', 'pending_review'], 'changes_requested', 'changes_requested', [
            'reviewer_id' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $comment,
        ], $comment);

        $this->notifyOwner($courseId, 'Changes Requested', 'Changes were requested on your course "%s": ' . $comment);
        return $result;
    }

    public function publish(int $courseId, int $userId): array
    {
        $this->guardPermission($userId, 'courses.publish');
        $result = $this->transition($courseId, $userId, ['approved', 'scheduled', 'unpublished'], 'published', 'published', [
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s'),
            'scheduled_publish_at' => null,
        ]);

        $this->notifyOwner($courseId, 'Course Published', 'Your course "%s" is now live on Beyond Barista Academy.');
        return $result;
    }

    public function schedule(int $courseId, int $userId, string $publishAt): array
    {
        $this->guardPermission($userId, 'courses.publish');
        $ts = strtotime($publishAt);
        if ($ts === false || $ts <= time()) {
            throw new RuntimeException('Scheduled publish date must be a valid future date/time.');
        }
        return $this->transition($courseId, $userId, ['approved'], 'scheduled', 'scheduled', [
            'scheduled_publish_at' => date('Y-m-d H:i:s', $ts),
        ]);
    }

    /**
     * Flips any course past its scheduled_publish_at into published.
     * Safe to call repeatedly (e.g. opportunistically from the public catalog,
     * or from a cPanel Cron Job hitting a small console script).
     */
    public function publishDueScheduled(): int
    {
        $due = Database::fetchAll(
            "SELECT id FROM courses WHERE status = 'scheduled' AND scheduled_publish_at IS NOT NULL AND scheduled_publish_at <= NOW()"
        );
        $count = 0;
        foreach ($due as $row) {
            try {
                $this->transition((int)$row['id'], null, ['scheduled'], 'published', 'published', [
                    'is_published' => 1,
                    'published_at' => date('Y-m-d H:i:s'),
                ]);
                $count++;
            } catch (RuntimeException) {
                // Row changed under us between the SELECT and transition; skip safely.
            }
        }
        return $count;
    }

    public function unpublish(int $courseId, int $userId): array
    {
        $this->guardPermission($userId, 'courses.publish');
        return $this->transition($courseId, $userId, ['published'], 'unpublished', 'unpublished', [
            'is_published' => 0,
        ]);
    }

    public function archive(int $courseId, int $userId): array
    {
        $this->guardPermission($userId, 'courses.archive');
        return $this->transition($courseId, $userId, [
            'draft', 'changes_requested', 'rejected', 'approved', 'scheduled', 'unpublished',
        ], 'archived', 'archived', [
            'is_published' => 0,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function restore(int $courseId, int $userId): array
    {
        $this->guardPermission($userId, 'courses.archive');
        return $this->transition($courseId, $userId, ['archived'], 'draft', 'restored', [
            'archived_at' => null,
        ]);
    }

    // ── Internals ───────────────────────────────────────────────────────────

    private function transition(int $courseId, ?int $userId, array $allowedFrom, string $toStatus, string $action, array $extraFields = [], ?string $comment = null): array
    {
        $course = Course::find($courseId);
        if (!$course) {
            throw new RuntimeException('Course not found.');
        }

        $fromStatus = $course['status'];
        if (!in_array($fromStatus, $allowedFrom, true)) {
            throw new RuntimeException("Cannot move course from \"{$fromStatus}\" to \"{$toStatus}\".");
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            Course::update($courseId, array_merge(['status' => $toStatus], $extraFields));

            Database::insert('course_approval_history', [
                'course_id' => $courseId,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action' => $action,
                'performed_by' => $userId ?? $course['created_by'],
                'comment' => $comment,
            ]);

            AuditLog::log('course_' . $action, 'course', $courseId, [
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'comment' => $comment,
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return Course::find($courseId);
    }

    private function guardPermission(int $userId, string $permissionSlug): void
    {
        if (!User::hasPermission($userId, $permissionSlug)) {
            throw new RuntimeException('You do not have permission to perform this action.');
        }
    }

    /**
     * The `courses.submit_review` permission only entitles a user to submit
     * courses they own; admins/super_admins may act on any course regardless
     * of ownership (mirrors InstructorController::canManageCourse()).
     */
    private function guardOwnerOrPermission(int $courseId, int $userId, string $permissionSlug): void
    {
        if (!User::hasPermission($userId, $permissionSlug)) {
            throw new RuntimeException('You do not have permission to perform this action.');
        }
        $course = Course::find($courseId);
        $isOwner = $course && (int)$course['created_by'] === $userId;
        $isAdmin = in_array(auth_role(), ['admin', 'super_admin'], true);
        if (!$isOwner && !$isAdmin) {
            throw new RuntimeException('You can only submit your own courses for review.');
        }
    }

    private function notifyOwner(int $courseId, string $title, string $messageTemplate): void
    {
        $course = Course::find($courseId);
        if (!$course) {
            return;
        }
        Notification::send(
            (int)$course['created_by'],
            $title,
            sprintf($messageTemplate, $course['title']),
            url('instructor/courses/' . $courseId . '/curriculum')
        );
    }
}
