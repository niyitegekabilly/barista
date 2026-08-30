-- ============================================================================
-- Beyond Barista Academy — LMS Finance, Orders & Payment System Migration
-- File: 005_upgrade_finance_and_orders.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Upgrade `orders` Table
ALTER TABLE `orders`
    MODIFY COLUMN `status` ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded', 'partially_refunded', 'failed') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `payment_status` ENUM('unpaid', 'pending', 'paid', 'partially_paid', 'failed', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'unpaid' AFTER `status`,
    ADD COLUMN IF NOT EXISTS `subtotal_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `user_id`,
    ADD COLUMN IF NOT EXISTS `tax_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `discount_amount`,
    ADD COLUMN IF NOT EXISTS `fee_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`,
    ADD COLUMN IF NOT EXISTS `billing_name` VARCHAR(150) NULL AFTER `currency`,
    ADD COLUMN IF NOT EXISTS `billing_email` VARCHAR(150) NULL AFTER `billing_name`,
    ADD COLUMN IF NOT EXISTS `billing_phone` VARCHAR(50) NULL AFTER `billing_email`,
    ADD COLUMN IF NOT EXISTS `billing_address` TEXT NULL AFTER `billing_phone`,
    ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) NULL AFTER `billing_address`,
    ADD COLUMN IF NOT EXISTS `customer_notes` TEXT NULL AFTER `payment_method`,
    ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL AFTER `customer_notes`;

-- Sync existing orders payment_status & subtotal if not set
UPDATE `orders` SET `subtotal_amount` = `total_amount` WHERE `subtotal_amount` = 0;
UPDATE `orders` SET `payment_status` = 'paid' WHERE `status` = 'completed';
UPDATE `orders` SET `payment_status` = 'pending' WHERE `status` = 'pending';
UPDATE `orders` SET `payment_status` = 'refunded' WHERE `status` = 'refunded';
UPDATE `orders` SET `payment_status` = 'failed' WHERE `status` = 'failed';

-- 2. Upgrade `order_items` Table
ALTER TABLE `order_items`
    ADD COLUMN IF NOT EXISTS `unit_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `item_title`,
    ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `price`,
    ADD COLUMN IF NOT EXISTS `tax_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `discount_amount`,
    ADD COLUMN IF NOT EXISTS `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`;

UPDATE `order_items` SET `unit_price` = `price`, `total_amount` = `price` WHERE `total_amount` = 0;

-- 3. Upgrade `payments` Table
ALTER TABLE `payments`
    MODIFY COLUMN `status` ENUM('pending', 'processing', 'successful', 'failed', 'cancelled', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `gateway` VARCHAR(50) NOT NULL DEFAULT 'sandbox' AFTER `payment_method`,
    ADD COLUMN IF NOT EXISTS `gateway_transaction_id` VARCHAR(150) NULL AFTER `transaction_reference`,
    ADD COLUMN IF NOT EXISTS `failure_reason` TEXT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `verified_at` TIMESTAMP NULL AFTER `failure_reason`,
    ADD COLUMN IF NOT EXISTS `refunded_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `amount`;

-- 4. Upgrade `coupons` Table
ALTER TABLE `coupons`
    ADD COLUMN IF NOT EXISTS `name` VARCHAR(120) NULL AFTER `code`,
    ADD COLUMN IF NOT EXISTS `scope` ENUM('global', 'course', 'category') NOT NULL DEFAULT 'global' AFTER `discount_value`,
    ADD COLUMN IF NOT EXISTS `course_id` INT UNSIGNED NULL AFTER `scope`,
    ADD COLUMN IF NOT EXISTS `category_id` INT UNSIGNED NULL AFTER `course_id`,
    ADD COLUMN IF NOT EXISTS `per_user_limit` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `max_uses`,
    ADD COLUMN IF NOT EXISTS `start_date` TIMESTAMP NULL AFTER `per_user_limit`;

-- 5. Create `refunds` Table
CREATE TABLE IF NOT EXISTS `refunds` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `refund_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_id` INT UNSIGNED NOT NULL,
    `payment_id` INT UNSIGNED NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF',
    `reason` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'processed', 'failed', 'rejected') NOT NULL DEFAULT 'processed',
    `gateway_refund_id` VARCHAR(150) NULL,
    `requested_by` INT UNSIGNED NULL,
    `approved_by` INT UNSIGNED NULL,
    `processed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_ref_order` (`order_id`),
    INDEX `idx_ref_number` (`refund_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Create `invoices` Table
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `subtotal` DECIMAL(12, 2) NOT NULL,
    `discount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `tax` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(12, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF',
    `status` ENUM('draft', 'issued', 'paid', 'cancelled', 'refunded') NOT NULL DEFAULT 'issued',
    `billing_info` JSON NULL,
    `due_date` DATE NULL,
    `paid_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_inv_num` (`invoice_number`),
    INDEX `idx_inv_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create `receipts` Table
CREATE TABLE IF NOT EXISTS `receipts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `receipt_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_id` INT UNSIGNED NOT NULL,
    `payment_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF',
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_reference` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_rec_num` (`receipt_number`),
    INDEX `idx_rec_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Create `financial_transactions` (Ledger) Table
CREATE TABLE IF NOT EXISTS `financial_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_id` INT UNSIGNED NULL,
    `payment_id` INT UNSIGNED NULL,
    `refund_id` INT UNSIGNED NULL,
    `user_id` INT UNSIGNED NULL,
    `type` ENUM('charge', 'refund', 'adjustment', 'payout', 'fee') NOT NULL DEFAULT 'charge',
    `amount` DECIMAL(12, 2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'RWF',
    `direction` ENUM('credit', 'debit') NOT NULL DEFAULT 'credit',
    `status` ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'completed',
    `reference` VARCHAR(150) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`refund_id`) REFERENCES `refunds`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_ft_type` (`type`),
    INDEX `idx_ft_direction` (`direction`),
    INDEX `idx_ft_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Create `order_notes` Table
CREATE TABLE IF NOT EXISTS `order_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `note` TEXT NOT NULL,
    `is_customer_visible` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_on_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Create `tax_rates` Table
CREATE TABLE IF NOT EXISTS `tax_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `rate_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    `country_code` VARCHAR(5) NOT NULL DEFAULT 'RW',
    `is_inclusive` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Create `payment_webhook_events` Table
CREATE TABLE IF NOT EXISTS `payment_webhook_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gateway` VARCHAR(50) NOT NULL,
    `event_id` VARCHAR(150) NOT NULL,
    `event_type` VARCHAR(100) NOT NULL,
    `payload` JSON NOT NULL,
    `status` ENUM('received', 'processed', 'ignored', 'failed') NOT NULL DEFAULT 'received',
    `processed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_gateway_event` (`gateway`, `event_id`),
    INDEX `idx_pwe_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Seed Granular Finance & Commerce Permissions
INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(19, 'View Finance Dashboard', 'finance.view', 'finance', 'View revenue metrics, financial charts, and commerce summaries'),
(20, 'View Finance Analytics', 'finance.dashboard', 'finance', 'Access detailed financial performance and sales reports'),
(21, 'View Orders', 'orders.view', 'orders', 'View customer orders list and details'),
(22, 'Manage Orders', 'orders.manage', 'orders', 'Update order status, cancel orders, and edit details'),
(23, 'View Payments', 'payments.view', 'payments', 'View payment transactions, gateways and references'),
(24, 'Manage Payments', 'payments.manage', 'payments', 'Verify, re-check or update payment status'),
(25, 'Process Refunds', 'payments.refund', 'payments', 'Execute full and partial payment refunds'),
(26, 'View Invoices', 'invoices.view', 'invoices', 'Access customer invoices and receipts'),
(27, 'Generate Invoices', 'invoices.generate', 'invoices', 'Create or regenerate official invoices and receipts'),
(28, 'View Financial Reports', 'reports.view', 'finance', 'View financial ledger and sales breakdowns'),
(29, 'Export Financial Reports', 'reports.export', 'finance', 'Export orders, payments, and financial CSV reports'),
(30, 'Create Manual Payments', 'manual_payments.create', 'payments', 'Record offline bank transfers and cash payments'),
(31, 'Verify Manual Payments', 'manual_payments.verify', 'payments', 'Approve offline bank payments and grant enrollment');

-- Assign permissions to Super Admin (1) and Admin (4)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions` WHERE `module` IN ('finance', 'orders', 'payments', 'invoices');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `slug` IN ('finance.view', 'finance.dashboard', 'orders.view', 'orders.manage', 'payments.view', 'invoices.view', 'reports.view', 'reports.export', 'manual_payments.create', 'manual_payments.verify');

-- 13. Seed Default Tax Rates (Educational Zero-Rated & Standard VAT)
INSERT IGNORE INTO `tax_rates` (`id`, `name`, `rate_percentage`, `country_code`, `is_inclusive`, `is_active`) VALUES
(1, 'Rwanda Educational Training Exemption', 0.00, 'RW', 1, 1),
(2, 'Rwanda Standard VAT', 18.00, 'RW', 1, 0);

SET FOREIGN_KEY_CHECKS = 1;
