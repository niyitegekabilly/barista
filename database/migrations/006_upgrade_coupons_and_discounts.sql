-- ====================================================================
-- Beyond Barista Academy — Migration 006: Upgrade Coupons & Discounts Promotion Engine
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Create `coupon_campaigns` Table
CREATE TABLE IF NOT EXISTS `coupon_campaigns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `banner_image` VARCHAR(255) NULL,
    `budget_limit` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `discount_spent` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `start_date` TIMESTAMP NULL,
    `end_date` TIMESTAMP NULL,
    `status` ENUM('draft', 'active', 'paused', 'completed', 'archived') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_camp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Upgrade `coupons` Table with Advanced Promotion Attributes
ALTER TABLE `coupons`
    ADD COLUMN IF NOT EXISTS `campaign_id` INT UNSIGNED NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF' AFTER `discount_value`,
    ADD COLUMN IF NOT EXISTS `max_discount_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `currency`,
    ADD COLUMN IF NOT EXISTS `user_eligibility` ENUM('all', 'new_users_only', 'existing_users_only', 'first_purchase_only', 'specific_users', 'membership_only') NOT NULL DEFAULT 'all' AFTER `scope`,
    ADD COLUMN IF NOT EXISTS `is_stackable` TINYINT(1) NOT NULL DEFAULT 0 AFTER `user_eligibility`,
    ADD COLUMN IF NOT EXISTS `sale_price_rule` ENUM('apply_to_sale_price', 'apply_to_regular_price', 'exclude_sale_items') NOT NULL DEFAULT 'apply_to_sale_price' AFTER `is_stackable`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'scheduled', 'expired', 'disabled', 'archived') NOT NULL DEFAULT 'active' AFTER `is_active`,
    ADD COLUMN IF NOT EXISTS `created_by` INT UNSIGNED NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
    ADD CONSTRAINT `fk_coupon_campaign` FOREIGN KEY IF NOT EXISTS (`campaign_id`) REFERENCES `coupon_campaigns`(`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_coupon_creator` FOREIGN KEY IF NOT EXISTS (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 3. Create `coupon_courses` Table (Inclusions & Exclusions)
CREATE TABLE IF NOT EXISTS `coupon_courses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `type` ENUM('include', 'exclude') NOT NULL DEFAULT 'include',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_coupon_course_type` (`coupon_id`, `course_id`, `type`),
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create `coupon_categories` Table (Inclusions & Exclusions)
CREATE TABLE IF NOT EXISTS `coupon_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `type` ENUM('include', 'exclude') NOT NULL DEFAULT 'include',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_coupon_category_type` (`coupon_id`, `category_id`, `type`),
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create `coupon_users` Table (Specific User Whitelist/Restriction)
CREATE TABLE IF NOT EXISTS `coupon_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_coupon_user` (`coupon_id`, `user_id`),
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create `coupon_redemptions` Table (Immutable Usage Ledger)
CREATE TABLE IF NOT EXISTS `coupon_redemptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NULL,
    `original_amount` DECIMAL(12, 2) NOT NULL,
    `discount_amount` DECIMAL(12, 2) NOT NULL,
    `final_amount` DECIMAL(12, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF',
    `redeemed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`campaign_id`) REFERENCES `coupon_campaigns`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    INDEX `idx_cr_coupon` (`coupon_id`),
    INDEX `idx_cr_user` (`user_id`),
    INDEX `idx_cr_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create `coupon_activity_logs` Table (Promotion Audit Trail)
CREATE TABLE IF NOT EXISTS `coupon_activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NULL,
    `campaign_id` INT UNSIGNED NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `coupon_campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_cal_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Seed Granular Coupons & Campaigns Permissions
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(32, 'View Coupons', 'coupons.view', 'coupons', 'View promotions, discounts and coupon codes'),
(33, 'Create Coupons', 'coupons.create', 'coupons', 'Create new promotional discount codes'),
(34, 'Update Coupons', 'coupons.update', 'coupons', 'Edit discount amounts, dates and eligibility rules'),
(35, 'Delete Coupons', 'coupons.delete', 'coupons', 'Safely remove unused coupons'),
(36, 'Activate / Disable Coupons', 'coupons.activate', 'coupons', 'Enable or disable promotional codes'),
(37, 'Archive Coupons', 'coupons.archive', 'coupons', 'Archive old promotional coupons'),
(38, 'Generate Bulk Coupons', 'coupons.generate', 'coupons', 'Generate batches of unique promotional codes'),
(39, 'View Redemptions', 'coupons.view_redemptions', 'coupons', 'Inspect chronological coupon redemptions history'),
(40, 'Export Promotions', 'coupons.export', 'coupons', 'Export coupon codes and redemptions CSV'),
(41, 'View Campaigns', 'campaigns.view', 'campaigns', 'View marketing campaigns and budget tracking'),
(42, 'Create Campaigns', 'campaigns.create', 'campaigns', 'Create marketing promotion campaigns'),
(43, 'Update Campaigns', 'campaigns.update', 'campaigns', 'Edit marketing campaign parameters'),
(44, 'Delete Campaigns', 'campaigns.delete', 'campaigns', 'Remove marketing campaigns'),
(45, 'View Campaign Analytics', 'campaigns.analytics', 'campaigns', 'Inspect marketing campaign ROI and sales performance');

-- Assign permissions to Super Admin (1) and Admin (4)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` WHERE `module` IN ('coupons', 'campaigns');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `module` IN ('coupons', 'campaigns');

SET FOREIGN_KEY_CHECKS = 1;
