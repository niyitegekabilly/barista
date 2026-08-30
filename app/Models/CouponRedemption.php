<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class CouponRedemption extends Model {
    protected static string $table = 'coupon_redemptions';

    public static function findWithDetails(int $id): ?array {
        $sql = "SELECT cr.*, c.code as coupon_code, c.name as coupon_name,
                       u.name as user_name, u.email as user_email,
                       o.order_number, crs.title as course_title, camp.name as campaign_name
                FROM coupon_redemptions cr
                JOIN coupons c ON cr.coupon_id = c.id
                JOIN users u ON cr.user_id = u.id
                JOIN orders o ON cr.order_id = o.id
                LEFT JOIN courses crs ON cr.course_id = crs.id
                LEFT JOIN coupon_campaigns camp ON cr.campaign_id = camp.id
                WHERE cr.id = :id LIMIT 1";
        return Database::fetchOne($sql, ['id' => $id]);
    }
}
