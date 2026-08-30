<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Coupon extends Model {
    protected static string $table = 'coupons';

    public static function findValid(string $code, float $amount = 0.0): ?array {
        $coupon = self::findBy('code', strtoupper(trim($code)));
        if (!$coupon || !$coupon['is_active']) {
            return null;
        }

        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
            return null;
        }

        if ($coupon['uses_count'] >= $coupon['max_uses']) {
            return null;
        }

        if ($amount > 0 && $amount < (float)$coupon['min_spend']) {
            return null;
        }

        return $coupon;
    }
}
