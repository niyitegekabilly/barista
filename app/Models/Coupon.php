<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Coupon extends Model {
    protected static string $table = 'coupons';

    /**
     * Validate a coupon against a course purchase.
     */
    public static function findValid(string $code, float $amount = 0.0, ?int $courseId = null, ?int $userId = null): array {
        $coupon = self::findBy('code', strtoupper(trim($code)));
        if (!$coupon || !$coupon['is_active']) {
            return ['valid' => false, 'message' => 'Coupon code is invalid or deactivated.'];
        }

        $now = time();
        if (!empty($coupon['start_date']) && strtotime($coupon['start_date']) > $now) {
            return ['valid' => false, 'message' => 'This coupon is not active yet.'];
        }

        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < $now) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon['max_uses'] > 0 && $coupon['uses_count'] >= $coupon['max_uses']) {
            return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        if ($amount > 0 && (float)$coupon['min_spend'] > 0 && $amount < (float)$coupon['min_spend']) {
            return ['valid' => false, 'message' => 'Minimum order amount for this coupon is ' . format_rwf((float)$coupon['min_spend'])];
        }

        // Per-user usage validation
        if ($userId && !empty($coupon['per_user_limit'])) {
            $userUses = (int)Database::fetchValue(
                "SELECT COUNT(*) FROM orders WHERE user_id = :uid AND coupon_id = :cid AND payment_status = 'paid'",
                ['uid' => $userId, 'cid' => $coupon['id']]
            );
            if ($userUses >= (int)$coupon['per_user_limit']) {
                return ['valid' => false, 'message' => 'You have already used this coupon code.'];
            }
        }

        // Scope validation (Course / Category)
        if ($coupon['scope'] === 'course' && !empty($coupon['course_id'])) {
            if (!$courseId || (int)$coupon['course_id'] !== $courseId) {
                return ['valid' => false, 'message' => 'This coupon is only valid for a specific course.'];
            }
        }

        // Calculate discount
        $discountValue = (float)$coupon['discount_value'];
        $discountAmount = 0.0;
        if ($coupon['discount_type'] === 'percentage') {
            $discountAmount = ($amount * min(100.0, $discountValue)) / 100.0;
        } else {
            $discountAmount = min($amount, $discountValue);
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => round($discountAmount, 2),
            'final_amount' => max(0.00, round($amount - $discountAmount, 2))
        ];
    }
}
