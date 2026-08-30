-- ====================================================================
-- Beyond Barista Academy — Migration 009: Upgrade Certificates & Assessment Quiz Engine
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Enhance `certificates` Table
ALTER TABLE `certificates`
    ADD COLUMN IF NOT EXISTS `template_type` ENUM('specialty_barista', 'classic_gold', 'modern_slate', 'executive_dark') NOT NULL DEFAULT 'specialty_barista' AFTER `course_title`,
    ADD COLUMN IF NOT EXISTS `instructor_name` VARCHAR(150) NULL AFTER `template_type`,
    ADD COLUMN IF NOT EXISTS `grade_score` DECIMAL(5, 2) NULL AFTER `instructor_name`,
    ADD COLUMN IF NOT EXISTS `grade_letter` VARCHAR(10) NULL AFTER `grade_score`,
    ADD COLUMN IF NOT EXISTS `public_hash` VARCHAR(64) NULL AFTER `grade_letter`,
    ADD COLUMN IF NOT EXISTS `revocation_reason` TEXT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `revoked_at` TIMESTAMP NULL AFTER `revocation_reason`,
    ADD COLUMN IF NOT EXISTS `expiry_date` DATE NULL AFTER `revoked_at`;

-- Populate any existing certificates missing public_hash
UPDATE `certificates` 
SET `public_hash` = SHA2(CONCAT(id, certificate_number, UNIX_TIMESTAMP(created_at), RAND()), 256) 
WHERE `public_hash` IS NULL OR `public_hash` = '';

-- Ensure public_hash is unique
ALTER TABLE `certificates`
    MODIFY COLUMN `public_hash` VARCHAR(64) NOT NULL,
    ADD UNIQUE KEY IF NOT EXISTS `unique_cert_public_hash` (`public_hash`);

-- 2. Enhance `quizzes` Table
ALTER TABLE `quizzes`
    ADD COLUMN IF NOT EXISTS `randomize_questions` TINYINT(1) NOT NULL DEFAULT 0 AFTER `max_attempts`,
    ADD COLUMN IF NOT EXISTS `show_correct_answers` TINYINT(1) NOT NULL DEFAULT 1 AFTER `randomize_questions`,
    ADD COLUMN IF NOT EXISTS `requires_passing_to_continue` TINYINT(1) NOT NULL DEFAULT 1 AFTER `show_correct_answers`;

-- 3. Enhance `quiz_questions` Table
ALTER TABLE `quiz_questions`
    ADD COLUMN IF NOT EXISTS `correct_feedback` TEXT NULL AFTER `explanation`,
    ADD COLUMN IF NOT EXISTS `incorrect_feedback` TEXT NULL AFTER `correct_feedback`;

-- 4. Enhance `quiz_attempts` Table
ALTER TABLE `quiz_attempts`
    ADD COLUMN IF NOT EXISTS `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `score_percentage`,
    ADD COLUMN IF NOT EXISTS `instructor_feedback` TEXT NULL AFTER `is_passed`,
    ADD COLUMN IF NOT EXISTS `graded_by` INT UNSIGNED NULL AFTER `instructor_feedback`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('in_progress', 'submitted', 'graded', 'passed', 'failed') NOT NULL DEFAULT 'graded' AFTER `graded_by`;

-- 5. Seed Granular Permissions for Certificates & Quizzes
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`, `created_at`) VALUES
(55, 'View Certificates', 'certificates.view', 'certificates', 'View issued certificates and verification records', NOW()),
(56, 'Issue Certificates', 'certificates.issue', 'certificates', 'Manually generate and issue official certificates', NOW()),
(57, 'Revoke Certificates', 'certificates.revoke', 'certificates', 'Revoke or cancel invalid certificates', NOW()),
(58, 'Export Certificates', 'certificates.export', 'certificates', 'Export certification logs and CSV reports', NOW()),
(59, 'Manage Quizzes', 'quizzes.manage', 'assessments', 'Create, edit, and configure assessment quizzes', NOW()),
(60, 'Grade Quizzes', 'quizzes.grade', 'assessments', 'Review and grade student quiz submissions and essays', NOW());

-- Assign permissions to Super Admin (1) and Admin (2)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions` WHERE id BETWEEN 55 AND 60;

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` WHERE id BETWEEN 55 AND 60;

-- Assign quiz management permissions to Instructor (3)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE id IN (55, 59, 60);

SET FOREIGN_KEY_CHECKS = 1;
