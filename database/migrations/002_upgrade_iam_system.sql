-- ============================================================================
-- Beyond Barista Academy — LMS Identity & Access Management (IAM) Migration
-- File: 002_upgrade_iam_system.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Upgrade `users` Table with IAM Columns & Statuses
-- Note: Check if columns exist before adding for safe idempotent execution
ALTER TABLE `users` 
    MODIFY COLUMN `status` ENUM('active', 'pending', 'suspended', 'locked', 'archived') NOT NULL DEFAULT 'active',
    ADD COLUMN IF NOT EXISTS `student_id` VARCHAR(50) NULL UNIQUE AFTER `role_id`,
    ADD COLUMN IF NOT EXISTS `instructor_id` VARCHAR(50) NULL UNIQUE AFTER `student_id`,
    ADD COLUMN IF NOT EXISTS `invitation_token` VARCHAR(100) NULL AFTER `remember_token`,
    ADD COLUMN IF NOT EXISTS `invitation_expires_at` DATETIME NULL AFTER `invitation_token`,
    ADD COLUMN IF NOT EXISTS `last_login_at` DATETIME NULL AFTER `email_verified_at`,
    ADD COLUMN IF NOT EXISTS `last_login_ip` VARCHAR(45) NULL AFTER `last_login_at`,
    ADD COLUMN IF NOT EXISTS `failed_login_attempts` INT NOT NULL DEFAULT 0 AFTER `last_login_ip`,
    ADD COLUMN IF NOT EXISTS `locked_until` DATETIME NULL AFTER `failed_login_attempts`;

-- 2. Upgrade `roles` Table
ALTER TABLE `roles`
    ADD COLUMN IF NOT EXISTS `is_system` TINYINT(1) NOT NULL DEFAULT 0 AFTER `description`;

-- 3. Upgrade `permissions` Table
ALTER TABLE `permissions`
    ADD COLUMN IF NOT EXISTS `module` VARCHAR(50) NOT NULL DEFAULT 'general' AFTER `slug`;

-- 4. Multi-Role Support Pivot Table `user_roles`
CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate `user_roles` from existing `users.role_id`
INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`, `is_primary`, `created_at`)
SELECT `id`, `role_id`, 1, NOW() FROM `users`;

-- 5. Cohorts & Batches Tables
CREATE TABLE IF NOT EXISTS `cohorts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `max_students` INT UNSIGNED NOT NULL DEFAULT 25,
    `status` ENUM('upcoming', 'active', 'completed', 'archived') NOT NULL DEFAULT 'active',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cohort_users` (
    `cohort_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role_in_cohort` ENUM('student', 'instructor', 'mentor') NOT NULL DEFAULT 'student',
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`cohort_id`, `user_id`),
    FOREIGN KEY (`cohort_id`) REFERENCES `cohorts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. User Administrative Notes Table
CREATE TABLE IF NOT EXISTS `user_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `author_id` INT UNSIGNED NULL,
    `note` TEXT NOT NULL,
    `type` ENUM('general', 'academic', 'disciplinary', 'financial') NOT NULL DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. User Login Sessions & Security Tracking Table
CREATE TABLE IF NOT EXISTS `user_logins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `status` ENUM('success', 'failed', 'locked') NOT NULL DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_logins_user` (`user_id`),
    INDEX `idx_user_logins_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. User Invitations & Onboarding Table
CREATE TABLE IF NOT EXISTS `invitations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `role_id` INT UNSIGNED NOT NULL DEFAULT 2,
    `cohort_id` INT UNSIGNED NULL,
    `token` VARCHAR(100) NOT NULL UNIQUE,
    `status` ENUM('pending', 'accepted', 'expired', 'revoked') NOT NULL DEFAULT 'pending',
    `invited_by` INT UNSIGNED NULL,
    `expires_at` DATETIME NOT NULL,
    `accepted_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`cohort_id`) REFERENCES `cohorts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`invited_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_invitations_token` (`token`),
    INDEX `idx_invitations_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Seed Granular Permissions
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(1, 'View Users Directory', 'users.view', 'users', 'Access and search users directory'),
(2, 'Create Users & Invitations', 'users.create', 'users', 'Create and invite new users'),
(3, 'Edit User Profiles', 'users.edit', 'users', 'Edit personal, role, and status info'),
(4, 'Delete & Archive Users', 'users.delete', 'users', 'Suspend, lock, and archive users'),
(5, 'Manage Roles & Permissions', 'roles.manage', 'users', 'Create and assign roles and permissions matrix'),
(6, 'Manage Cohorts & Groups', 'cohorts.manage', 'cohorts', 'Create cohorts and assign students'),
(7, 'Manage Course Enrollments', 'enrollments.manage', 'courses', 'Manually enroll and drop students from courses'),
(8, 'View Financial Orders', 'finance.view', 'finance', 'View orders, invoices, and payment logs'),
(9, 'Issue & Revoke Certificates', 'certificates.manage', 'certificates', 'Issue, verify, and revoke student certificates'),
(10, 'View Security Audit Logs', 'audit.view', 'security', 'Access security audit trail and login logs');

-- Grant all permissions to Super Admin (Role ID 1) and Admin (Role ID 4)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `slug` IN ('users.view', 'users.create', 'users.edit', 'cohorts.manage', 'enrollments.manage', 'certificates.manage', 'finance.view');

-- Set system roles flag
UPDATE `roles` SET `is_system` = 1 WHERE `slug` IN ('super_admin', 'student', 'instructor', 'admin', 'moderator');

-- Ensure sample cohorts exist for immediate usage
INSERT IGNORE INTO `cohorts` (`id`, `name`, `code`, `start_date`, `end_date`, `max_students`, `status`, `description`) VALUES
(1, 'Barista Pro Masterclass — Q1 2026', 'BBA-2026-Q1', '2026-01-15', '2026-04-15', 30, 'active', 'Flagship Kigali barista foundation and advanced latte art batch.'),
(2, 'Specialty Roasting & Cupping Batch 2', 'BBA-ROAST-02', '2026-03-01', '2026-05-30', 20, 'upcoming', 'Sensory judging, SCA green coffee analysis, and sample roasting batch.'),
(3, 'Cafe Management & Hospitality Fellowship', 'BBA-MGMT-2026', '2026-02-01', '2026-06-30', 25, 'active', 'Hospitality operations, cost control, and coffee shop leadership cohort.');

-- Generate student and instructor IDs for existing users if missing
UPDATE `users` SET `student_id` = CONCAT('BBA-STU-2026-', LPAD(id, 4, '0')) WHERE `student_id` IS NULL AND `role_id` = 2;
UPDATE `users` SET `instructor_id` = CONCAT('BBA-INS-', LPAD(id, 4, '0')) WHERE `instructor_id` IS NULL AND `role_id` = 3;

SET FOREIGN_KEY_CHECKS = 1;
