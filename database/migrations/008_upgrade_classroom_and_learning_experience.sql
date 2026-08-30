-- ====================================================================
-- Beyond Barista Academy — Migration 008: Upgrade Classroom & Learning Experience
-- ====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Upgrade `lesson_progress` Table
ALTER TABLE `lesson_progress`
    ADD COLUMN IF NOT EXISTS `playback_speed` DECIMAL(3, 2) NOT NULL DEFAULT 1.00 AFTER `last_position_seconds`,
    ADD COLUMN IF NOT EXISTS `time_spent_seconds` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `playback_speed`,
    ADD COLUMN IF NOT EXISTS `last_watched_at` TIMESTAMP NULL AFTER `time_spent_seconds`;

-- 2. Upgrade `enrollments` Table
ALTER TABLE `enrollments`
    ADD COLUMN IF NOT EXISTS `current_streak_days` INT NOT NULL DEFAULT 1 AFTER `progress_percent`,
    ADD COLUMN IF NOT EXISTS `last_activity_date` DATE NULL AFTER `current_streak_days`,
    ADD COLUMN IF NOT EXISTS `total_time_spent_seconds` INT NOT NULL DEFAULT 0 AFTER `last_activity_date`;

-- 3. Upgrade `lesson_resources` (Attachments) Table
ALTER TABLE `lesson_resources`
    ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `title`,
    ADD COLUMN IF NOT EXISTS `download_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `file_size`,
    ADD COLUMN IF NOT EXISTS `is_external` TINYINT(1) NOT NULL DEFAULT 0 AFTER `download_count`,
    ADD COLUMN IF NOT EXISTS `external_url` VARCHAR(255) NULL AFTER `is_external`;

-- 4. Create `lesson_notes` Table (Private Student Notes)
CREATE TABLE IF NOT EXISTS `lesson_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `lesson_id` INT UNSIGNED NOT NULL,
    `note_text` TEXT NOT NULL,
    `timestamp_seconds` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE,
    INDEX `idx_ln_user_lesson` (`user_id`, `lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Create `lesson_discussions` Table (Community Q&A)
CREATE TABLE IF NOT EXISTS `lesson_discussions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `lesson_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED NULL,
    `question` TEXT NOT NULL,
    `is_answered` TINYINT(1) NOT NULL DEFAULT 0,
    `is_instructor_reply` TINYINT(1) NOT NULL DEFAULT 0,
    `upvotes` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `lesson_discussions`(`id`) ON DELETE CASCADE,
    INDEX `idx_ld_lesson` (`lesson_id`, `parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create `lesson_bookmarks` Table (Saved Video Moments)
CREATE TABLE IF NOT EXISTS `lesson_bookmarks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `lesson_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `timestamp_seconds` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_lesson_timestamp` (`user_id`, `lesson_id`, `timestamp_seconds`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
