<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Coupon extends Model {
    protected static string $table = 'coupons';

    /**
     * Find coupon with full relations, campaign, inclusions/exclusions, and metrics.
     */
    public static function findWithRelations(int $id): ?array {
        $coupon = Database::fetchOne(
            "SELECT c.*, camp.name as campaign_name, camp.slug as campaign_slug, u.name as creator_name
             FROM coupons c
             LEFT JOIN coupon_campaigns camp ON c.campaign_id = camp.id
             LEFT JOIN users u ON c.created_by = u.id
             WHERE c.id = :id LIMIT 1",
            ['id' => $id]
        );
        if (!$coupon) return null;

        // Inclusions & Exclusions
        $coupon['included_courses'] = Database::fetchAll(
            "SELECT c.id, c.title, c.slug, c.price FROM coupon_courses cc JOIN courses c ON cc.course_id = c.id WHERE cc.coupon_id = :id AND cc.type = 'include'",
            ['id' => $id]
        );
        $coupon['excluded_courses'] = Database::fetchAll(
            "SELECT c.id, c.title, c.slug, c.price FROM coupon_courses cc JOIN courses c ON cc.course_id = c.id WHERE cc.coupon_id = :id AND cc.type = 'exclude'",
            ['id' => $id]
        );

        $coupon['included_categories'] = Database::fetchAll(
            "SELECT cat.id, cat.name, cat.slug FROM coupon_categories cc JOIN categories cat ON cc.category_id = cat.id WHERE cc.coupon_id = :id AND cc.type = 'include'",
            ['id' => $id]
        );
        $coupon['excluded_categories'] = Database::fetchAll(
            "SELECT cat.id, cat.name, cat.slug FROM coupon_categories cc JOIN categories cat ON cc.category_id = cat.id WHERE cc.coupon_id = :id AND cc.type = 'exclude'",
            ['id' => $id]
        );

        $coupon['restricted_users'] = Database::fetchAll(
            "SELECT u.id, u.name, u.email FROM coupon_users cu JOIN users u ON cu.user_id = u.id WHERE cu.coupon_id = :id",
            ['id' => $id]
        );

        // Performance & Usage Metrics
        $coupon['redemptions_count'] = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :id", ['id' => $id]) ?: 0);
        $coupon['total_discount_given'] = (float)(Database::fetchValue("SELECT COALESCE(SUM(discount_amount), 0) FROM coupon_redemptions WHERE coupon_id = :id", ['id' => $id]) ?: 0.0);
        $coupon['total_revenue_generated'] = (float)(Database::fetchValue("SELECT COALESCE(SUM(final_amount), 0) FROM coupon_redemptions WHERE coupon_id = :id", ['id' => $id]) ?: 0.0);

        // Computed status
        $coupon['computed_status'] = static::getComputedStatus($coupon);

        return $coupon;
    }

    /**
     * Compute real-time status of coupon.
     */
    public static function getComputedStatus(array $coupon): string {
        if (empty($coupon['is_active']) || $coupon['status'] === 'disabled') {
            return 'disabled';
        }
        if ($coupon['status'] === 'archived') {
            return 'archived';
        }

        $now = time();
        if (!empty($coupon['start_date']) && strtotime($coupon['start_date']) > $now) {
            return 'scheduled';
        }
        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < $now) {
            return 'expired';
        }
        if ($coupon['max_uses'] > 0 && $coupon['uses_count'] >= $coupon['max_uses']) {
            return 'depleted';
        }

        return 'active';
    }

    /**
     * Generate a cryptographically random, collision-safe unique coupon code.
     */
    public static function generateCode(string $prefix = 'BBA', int $length = 8, string $charset = 'ALPHANUMERIC'): string {
        $prefix = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $prefix)));
        
        $chars = match ($charset) {
            'ALPHA' => 'ABCDEFGHJKLMNPQRSTUVWXYZ', // Avoid I, O
            'NUMERIC' => '23456789',               // Avoid 0, 1
            default => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'
        };

        $maxAttempts = 50;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $randomPart = '';
            for ($i = 0; $i < $length; $i++) {
                $randomPart .= $chars[random_int(0, strlen($chars) - 1)];
            }

            $code = $prefix ? ($prefix . $randomPart) : $randomPart;
            $exists = Database::fetchOne("SELECT id FROM coupons WHERE code = :c LIMIT 1", ['c' => $code]);
            if (!$exists) {
                return $code;
            }
        }

        return ($prefix ?: 'CODE') . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Log coupon activity to audit trail.
     */
    public static function logActivity(?int $couponId, ?int $campaignId, string $action, array $details = [], ?int $userId = null): void {
        Database::insert('coupon_activity_logs', [
            'coupon_id'   => $couponId,
            'campaign_id' => $campaignId,
            'user_id'     => $userId ?: auth_id(),
            'action'      => $action,
            'details'     => json_encode($details),
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Backward-compatible validator delegating to CouponService.
     */
    public static function findValid(string $code, float $amount = 0.0, ?int $courseId = null, ?int $userId = null): array {
        return \App\Services\CouponService::validateCoupon($code, $amount, $courseId, $userId);
    }
}
