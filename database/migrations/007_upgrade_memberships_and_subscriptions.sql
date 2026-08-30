-- ====================================================================
-- Beyond Barista Academy — Migration 007: Upgrade Memberships & Subscriptions System
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Upgrade `membership_plans` Table
ALTER TABLE `membership_plans`
    ADD COLUMN IF NOT EXISTS `tier_level` INT NOT NULL DEFAULT 1 AFTER `billing_interval`,
    ADD COLUMN IF NOT EXISTS `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF' AFTER `price`,
    ADD COLUMN IF NOT EXISTS `trial_period_days` INT NOT NULL DEFAULT 0 AFTER `tier_level`,
    ADD COLUMN IF NOT EXISTS `grace_period_days` INT NOT NULL DEFAULT 3 AFTER `trial_period_days`,
    ADD COLUMN IF NOT EXISTS `course_access_type` ENUM('all_courses', 'specific_courses', 'specific_categories', 'course_limit_per_month') NOT NULL DEFAULT 'all_courses' AFTER `grace_period_days`,
    ADD COLUMN IF NOT EXISTS `course_limit` INT NOT NULL DEFAULT 0 AFTER `course_access_type`,
    ADD COLUMN IF NOT EXISTS `discount_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 0.00 AFTER `course_limit`,
    ADD COLUMN IF NOT EXISTS `has_certificate_access` TINYINT(1) NOT NULL DEFAULT 1 AFTER `discount_percentage`,
    ADD COLUMN IF NOT EXISTS `has_live_workshops` TINYINT(1) NOT NULL DEFAULT 0 AFTER `has_certificate_access`,
    ADD COLUMN IF NOT EXISTS `has_job_board_priority` TINYINT(1) NOT NULL DEFAULT 0 AFTER `has_live_workshops`,
    ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `has_job_board_priority`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'hidden', 'archived') NOT NULL DEFAULT 'active' AFTER `is_active`;

-- 2. Create `membership_plan_courses` Table
CREATE TABLE IF NOT EXISTS `membership_plan_courses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_plan_course` (`plan_id`, `course_id`),
    FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create `membership_plan_categories` Table
CREATE TABLE IF NOT EXISTS `membership_plan_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_plan_category` (`plan_id`, `category_id`),
    FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Upgrade `memberships` (Subscriptions) Table
ALTER TABLE `memberships`
    ADD COLUMN IF NOT EXISTS `subscription_number` VARCHAR(50) NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `order_id` INT UNSIGNED NULL AFTER `plan_id`,
    ADD COLUMN IF NOT EXISTS `auto_renew` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `renewal_date` DATE NULL AFTER `end_date`,
    ADD COLUMN IF NOT EXISTS `trial_ends_at` TIMESTAMP NULL AFTER `renewal_date`,
    ADD COLUMN IF NOT EXISTS `grace_period_ends_at` TIMESTAMP NULL AFTER `trial_ends_at`,
    ADD COLUMN IF NOT EXISTS `cancelled_at` TIMESTAMP NULL AFTER `grace_period_ends_at`,
    ADD COLUMN IF NOT EXISTS `cancellation_reason` TEXT NULL AFTER `cancelled_at`,
    MODIFY COLUMN `status` ENUM('trialing', 'active', 'grace_period', 'expired', 'cancelled', 'paused') NOT NULL DEFAULT 'active',
    ADD CONSTRAINT `fk_membership_order` FOREIGN KEY IF NOT EXISTS (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL;

-- Populate subscription_number for existing records if null
UPDATE `memberships` SET `subscription_number` = CONCAT('BBA-SUB-', DATE_FORMAT(created_at, '%Y%m'), '-', LPAD(id, 4, '0')) WHERE `subscription_number` IS NULL;

-- 5. Create `membership_renewals` Table
CREATE TABLE IF NOT EXISTS `membership_renewals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `membership_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF',
    `status` ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'success',
    `billing_date` DATE NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`membership_id`) REFERENCES `memberships`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    INDEX `idx_mr_membership` (`membership_id`),
    INDEX `idx_mr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create `membership_activity_logs` Table
CREATE TABLE IF NOT EXISTS `membership_activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `membership_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`membership_id`) REFERENCES `memberships`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_mal_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Seed Granular Membership Permissions
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(46, 'View Memberships', 'memberships.view', 'memberships', 'View active student subscriptions and recurring billing'),
(47, 'Manage Memberships', 'memberships.manage', 'memberships', 'Extend, pause or adjust student subscriptions'),
(48, 'Cancel Memberships', 'memberships.cancel', 'memberships', 'Cancel student subscriptions and handle churn'),
(49, 'Extend Memberships', 'memberships.extend', 'memberships', 'Manually grant grace period or date extensions'),
(50, 'View Membership Plans', 'plans.view', 'memberships', 'View pricing tiers and membership plans'),
(51, 'Create Membership Plans', 'plans.create', 'memberships', 'Create new subscription tiers and perk packages'),
(52, 'Update Membership Plans', 'plans.update', 'memberships', 'Edit subscription plan pricing and course gating rules'),
(53, 'Delete Membership Plans', 'plans.delete', 'memberships', 'Safely remove or archive subscription plans'),
(54, 'View Subscription Analytics', 'subscriptions.analytics', 'memberships', 'View MRR, ARR, churn rate, and subscriber growth');

-- Assign permissions to Super Admin (1) and Admin (4)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` WHERE `module` = 'memberships';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `module` = 'memberships';

SET FOREIGN_KEY_CHECKS = 1;
