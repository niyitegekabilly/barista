-- ============================================================================
-- Beyond Barista Academy — Course Lifecycle & Approval Workflow Migration
-- File: 003_course_lifecycle_and_approval.sql
-- Additive/idempotent — safe to re-run. Does not drop or destructively alter
-- any existing table. `is_published` is kept (existing read paths depend on
-- it); the new `status` column becomes the real source of truth and the
-- application layer (CourseApprovalService) keeps `is_published` in sync.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Course lifecycle columns
ALTER TABLE `courses`
    ADD COLUMN IF NOT EXISTS `status` ENUM(
        'draft', 'pending_review', 'under_review', 'changes_requested',
        'approved', 'scheduled', 'published', 'unpublished', 'archived', 'rejected'
    ) NOT NULL DEFAULT 'draft' AFTER `is_published`,
    ADD COLUMN IF NOT EXISTS `submitted_by` INT UNSIGNED NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `submitted_at` TIMESTAMP NULL AFTER `submitted_by`,
    ADD COLUMN IF NOT EXISTS `reviewer_id` INT UNSIGNED NULL AFTER `submitted_at`,
    ADD COLUMN IF NOT EXISTS `reviewed_at` TIMESTAMP NULL AFTER `reviewer_id`,
    ADD COLUMN IF NOT EXISTS `rejection_reason` TEXT NULL AFTER `reviewed_at`,
    ADD COLUMN IF NOT EXISTS `scheduled_publish_at` DATETIME NULL AFTER `rejection_reason`,
    ADD COLUMN IF NOT EXISTS `published_at` TIMESTAMP NULL AFTER `scheduled_publish_at`,
    ADD COLUMN IF NOT EXISTS `archived_at` TIMESTAMP NULL AFTER `published_at`;

ALTER TABLE `courses`
    ADD CONSTRAINT `fk_courses_submitted_by` FOREIGN KEY IF NOT EXISTS (`submitted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_courses_reviewer_id` FOREIGN KEY IF NOT EXISTS (`reviewer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `courses`
    ADD INDEX IF NOT EXISTS `idx_courses_status` (`status`);

-- Backfill legacy rows once (guarded by submitted_at IS NULL so re-running
-- this file after real workflow activity never clobbers real state).
UPDATE `courses`
    SET `status` = 'published', `published_at` = `updated_at`
    WHERE `is_published` = 1 AND `status` = 'draft' AND `submitted_at` IS NULL;

-- 2. Approval history — full audit trail shown on the review page
CREATE TABLE IF NOT EXISTS `course_approval_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT UNSIGNED NOT NULL,
    `from_status` VARCHAR(30) NULL,
    `to_status` VARCHAR(30) NOT NULL,
    `action` VARCHAR(30) NOT NULL,
    `performed_by` INT UNSIGNED NOT NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_course_approval_history_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. New "Course Reviewer" role (id continues the existing 1-5 convention)
INSERT IGNORE INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`) VALUES
(6, 'Course Reviewer', 'reviewer', 'Reviews submitted courses for quality and approves, rejects, or requests changes. Cannot modify course content.', 1);

-- 4. New granular course-lifecycle permissions (ids continue from the
-- existing 1-10 seeded in 002_upgrade_iam_system.sql)
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(11, 'View Courses & Approval Queue', 'courses.view', 'courses', 'View the admin course list, KPIs, and course review detail pages'),
(12, 'Edit Course Content', 'courses.edit', 'courses', 'Create and edit course information, curriculum, lessons, and quizzes'),
(13, 'Submit Course for Review', 'courses.submit_review', 'courses', 'Submit a draft or changes-requested course into the review queue'),
(14, 'Review Submitted Courses', 'courses.review', 'courses', 'Approve, reject, or request changes on a submitted course'),
(15, 'Publish & Schedule Courses', 'courses.publish', 'courses', 'Publish, schedule, or unpublish an approved course'),
(16, 'Archive Courses', 'courses.archive', 'courses', 'Archive or restore a course');

-- Super Admin (1) and Admin (4): full course-lifecycle permission set
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` WHERE `slug` LIKE 'courses.%';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `slug` LIKE 'courses.%';

-- Instructor (3): can submit their own work for review. Deliberately NOT
-- granted courses.view/courses.edit — those gate the admin-wide course list
-- and detail pages (/admin/courses*), which must stay scoped to admin/
-- reviewer only. An instructor's own courses remain reachable via the
-- separate /instructor/courses routes + ownership check, independent of
-- this permission system. Instructor cannot review, publish, or archive
-- unless an admin explicitly grants it via /admin/roles.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, `id` FROM `permissions` WHERE `slug` IN ('courses.submit_review');

-- Clean up an earlier, over-broad grant if this migration was already applied
-- before this correction (see comment above) — safe/no-op on a fresh install.
DELETE FROM `role_permissions` WHERE `role_id` = 3 AND `permission_id` IN (
    SELECT id FROM `permissions` WHERE `slug` IN ('courses.view', 'courses.edit')
);

-- Reviewer (6): can view the queue and review, but cannot edit content.
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, `id` FROM `permissions` WHERE `slug` IN ('courses.view', 'courses.review');

SET FOREIGN_KEY_CHECKS = 1;
