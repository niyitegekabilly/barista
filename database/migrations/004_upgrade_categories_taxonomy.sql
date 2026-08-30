-- ============================================================================
-- Beyond Barista Academy — LMS Category & Taxonomy Management System Migration
-- File: 004_upgrade_categories_taxonomy.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Upgrade `categories` Table with Hierarchy, Media, Status, and SEO Metadata
ALTER TABLE `categories`
    MODIFY COLUMN `name` VARCHAR(150) NOT NULL,
    MODIFY COLUMN `slug` VARCHAR(160) NOT NULL,
    ADD COLUMN IF NOT EXISTS `short_description` VARCHAR(350) NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `color` VARCHAR(30) NOT NULL DEFAULT '#4C3103' AFTER `icon`,
    ADD COLUMN IF NOT EXISTS `thumbnail` VARCHAR(255) NULL AFTER `color`,
    ADD COLUMN IF NOT EXISTS `cover_image` VARCHAR(255) NULL AFTER `thumbnail`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('draft', 'active', 'inactive', 'archived') NOT NULL DEFAULT 'active' AFTER `sort_order`,
    ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `seo_title` VARCHAR(200) NULL AFTER `is_featured`,
    ADD COLUMN IF NOT EXISTS `seo_description` TEXT NULL AFTER `seo_title`,
    ADD COLUMN IF NOT EXISTS `seo_keywords` VARCHAR(255) NULL AFTER `seo_description`,
    ADD COLUMN IF NOT EXISTS `canonical_url` VARCHAR(255) NULL AFTER `seo_keywords`,
    ADD COLUMN IF NOT EXISTS `created_by` INT UNSIGNED NULL AFTER `canonical_url`,
    ADD COLUMN IF NOT EXISTS `updated_by` INT UNSIGNED NULL AFTER `created_by`;

-- Add foreign keys for author attribution if not existing
-- (Safe index additions)
ALTER TABLE `categories`
    ADD INDEX IF NOT EXISTS `idx_categories_parent` (`parent_id`),
    ADD INDEX IF NOT EXISTS `idx_categories_status` (`status`),
    ADD INDEX IF NOT EXISTS `idx_categories_featured` (`is_featured`);

-- Sync legacy `is_active` to `status`
UPDATE `categories` SET `status` = 'active' WHERE `status` IS NULL OR `status` = '';
UPDATE `categories` SET `status` = 'inactive' WHERE `is_active` = 0;

-- 2. Multi-Category Course Pivot Table `course_categories`
CREATE TABLE IF NOT EXISTS `course_categories` (
    `course_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`course_id`, `category_id`),
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    INDEX `idx_course_cat_primary` (`is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill `course_categories` with existing primary courses
INSERT IGNORE INTO `course_categories` (`course_id`, `category_id`, `is_primary`, `created_at`)
SELECT `id`, `category_id`, 1, NOW() FROM `courses` WHERE `category_id` IS NOT NULL;

-- 3. Tags & Taxonomies Tables
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(110) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `usage_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tags_slug` (`slug`),
    INDEX `idx_tags_usage` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_tags` (
    `course_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`course_id`, `tag_id`),
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Category Activity Logs Table
CREATE TABLE IF NOT EXISTS `category_activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_cat_act_cat` (`category_id`),
    INDEX `idx_cat_act_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Seed Granular Category & Taxonomy Permissions
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(11, 'View Categories', 'categories.view', 'categories', 'View category directory and hierarchy'),
(12, 'Create Categories', 'categories.create', 'categories', 'Create parent categories and subcategories'),
(13, 'Update Categories', 'categories.update', 'categories', 'Edit category details, hierarchy and status'),
(14, 'Delete Categories', 'categories.delete', 'categories', 'Safely delete or reassign categories'),
(15, 'Archive Categories', 'categories.archive', 'categories', 'Archive categories to preserve course history'),
(16, 'Manage Category Courses', 'categories.manage_courses', 'categories', 'Reassign and map courses across categories'),
(17, 'Manage Tags & Taxonomy', 'categories.manage_tags', 'categories', 'Create, edit, and organize taxonomy tags'),
(18, 'Manage Category SEO', 'categories.manage_seo', 'categories', 'Configure SEO titles, descriptions, and metadata');

-- Grant permissions to Super Admin (1) and Admin (4)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` WHERE `module` = 'categories';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `slug` IN ('categories.view', 'categories.create', 'categories.update', 'categories.manage_courses', 'categories.manage_tags', 'categories.manage_seo');

-- 6. Seed Specialty Coffee Tags
INSERT IGNORE INTO `tags` (`name`, `slug`, `description`, `usage_count`) VALUES
('Espresso Theory', 'espresso-theory', 'Espresso extraction, yield ratios, and dial-in mechanics', 4),
('Latte Art Masterclass', 'latte-art', 'Milk steaming textures, free-pour patterns, and microfoam science', 6),
('Sensory Cupping', 'sensory-cupping', 'SCA cupping protocols, flavor wheel navigation, and sensory scoring', 3),
('Coffee Roasting', 'coffee-roasting', 'Green bean selection, roast curves, charge temperatures, and development time', 2),
('Barista Skills', 'barista-skills', 'Workflow efficiency, grinder calibration, and customer service excellence', 8),
('Hospitality & Cafe Management', 'cafe-management', 'Cost control, inventory systems, team leadership, and profit optimization', 5),
('Brewing Methods', 'brewing-methods', 'V60, AeroPress, Chemex, French Press, and cold brew extraction dynamics', 4),
('Milk Science', 'milk-science', 'Proteins, fats, milk alternatives, and temperature stabilization', 3),
('SCA Certification', 'sca-certification', 'Specialty Coffee Association standardized curriculum and practical exam prep', 5),
('Food Safety & Hygiene', 'food-safety', 'HACCP protocols, sanitization standards, and workstation ergonomics', 3);

-- Update sample categories with subcategories and rich data if needed
UPDATE `categories` SET `is_featured` = 1, `color` = '#4C3103', `short_description` = 'Master professional espresso extraction, grinder calibration, and sensory dial-in standards.' WHERE `slug` = 'barista-skills' OR `name` LIKE '%Barista%';
UPDATE `categories` SET `is_featured` = 1, `color` = '#D97706', `short_description` = 'Microfoam texturing, milk steaming physics, and precision latte art patterns.' WHERE `slug` = 'latte-art' OR `name` LIKE '%Latte%';
UPDATE `categories` SET `is_featured` = 1, `color` = '#2563EB', `short_description` = 'Café financial operations, hospitality workflow, customer service, and shop leadership.' WHERE `slug` = 'cafe-management' OR `name` LIKE '%Management%';

SET FOREIGN_KEY_CHECKS = 1;
