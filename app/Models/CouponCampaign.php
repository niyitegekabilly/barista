<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class CouponCampaign extends Model {
    protected static string $table = 'coupon_campaigns';

    public static function findBySlug(string $slug): ?array {
        return Database::fetchOne("SELECT * FROM coupon_campaigns WHERE slug = :s LIMIT 1", ['s' => trim($slug)]);
    }

    public static function findWithMetrics(int $id): ?array {
        $camp = Database::fetchOne("SELECT c.*, u.name as creator_name FROM coupon_campaigns c LEFT JOIN users u ON c.created_by = u.id WHERE c.id = :id", ['id' => $id]);
        if (!$camp) return null;

        $camp['coupons_count'] = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupons WHERE campaign_id = :id", ['id' => $id]) ?: 0);
        $camp['redemptions_count'] = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupon_redemptions WHERE campaign_id = :id", ['id' => $id]) ?: 0);
        $camp['total_discount'] = (float)(Database::fetchValue("SELECT COALESCE(SUM(discount_amount), 0) FROM coupon_redemptions WHERE campaign_id = :id", ['id' => $id]) ?: 0.0);
        $camp['total_revenue'] = (float)(Database::fetchValue("SELECT COALESCE(SUM(final_amount), 0) FROM coupon_redemptions WHERE campaign_id = :id", ['id' => $id]) ?: 0.0);
        $camp['coupons'] = Database::fetchAll("SELECT * FROM coupons WHERE campaign_id = :id ORDER BY created_at DESC", ['id' => $id]);

        return $camp;
    }
}
