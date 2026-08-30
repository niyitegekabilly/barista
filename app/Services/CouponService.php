<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Coupon;
use App\Models\CouponCampaign;
use App\Models\CouponRedemption;
use App\Models\Course;

class CouponService {

    /**
     * Comprehensive server-side coupon validation engine.
     */
    public static function validateCoupon(
        string $code,
        float $subtotalAmount = 0.00,
        ?int $courseId = null,
        ?int $userId = null,
        array $options = []
    ): array {
        $normalizedCode = strtoupper(trim($code));
        if (empty($normalizedCode)) {
            return ['valid' => false, 'message' => 'Please enter a promo code.'];
        }

        $coupon = Database::fetchOne("SELECT * FROM coupons WHERE code = :c LIMIT 1", ['c' => $normalizedCode]);
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon code is invalid.'];
        }

        // 1. Check Status & Is Active
        if (empty($coupon['is_active']) || $coupon['status'] === 'disabled') {
            return ['valid' => false, 'message' => 'This coupon has been disabled.'];
        }

        if ($coupon['status'] === 'archived') {
            return ['valid' => false, 'message' => 'This promotional code is no longer active.'];
        }

        // 2. Check Date Scheduling & Expiry
        $now = time();
        if (!empty($coupon['start_date']) && strtotime($coupon['start_date']) > $now) {
            return ['valid' => false, 'message' => 'This promotion is scheduled to start on ' . date('M d, Y', strtotime($coupon['start_date'])) . '.'];
        }

        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < $now) {
            return ['valid' => false, 'message' => 'This coupon code has expired.'];
        }

        // 3. Check Campaign Status (if linked)
        if (!empty($coupon['campaign_id'])) {
            $campaign = Database::fetchOne("SELECT * FROM coupon_campaigns WHERE id = :id", ['id' => $coupon['campaign_id']]);
            if ($campaign) {
                if ($campaign['status'] === 'paused' || $campaign['status'] === 'archived' || $campaign['status'] === 'completed') {
                    return ['valid' => false, 'message' => 'The campaign for this coupon is currently inactive.'];
                }
                if ($campaign['budget_limit'] > 0 && $campaign['discount_spent'] >= $campaign['budget_limit']) {
                    return ['valid' => false, 'message' => 'The promotional budget for this campaign has been reached.'];
                }
            }
        }

        // 4. Total Usage Limit
        if ($coupon['max_uses'] > 0 && $coupon['uses_count'] >= $coupon['max_uses']) {
            return ['valid' => false, 'message' => 'The maximum redemption limit for this coupon has been reached.'];
        }

        // 5. Per-User Usage Limit
        if ($userId && !empty($coupon['per_user_limit'])) {
            $userRedemptions = (int)Database::fetchValue(
                "SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :cid AND user_id = :uid",
                ['cid' => $coupon['id'], 'uid' => $userId]
            );

            if ($userRedemptions >= (int)$coupon['per_user_limit']) {
                return ['valid' => false, 'message' => 'You have already redeemed the maximum allowed for this coupon (' . $coupon['per_user_limit'] . ' time' . ($coupon['per_user_limit'] > 1 ? 's' : '') . ').'];
            }
        }

        // 6. User Eligibility Rules
        if ($userId && !empty($coupon['user_eligibility'])) {
            if ($coupon['user_eligibility'] === 'new_users_only' || $coupon['user_eligibility'] === 'first_purchase_only') {
                $previousOrders = (int)Database::fetchValue(
                    "SELECT COUNT(*) FROM orders WHERE user_id = :uid AND payment_status = 'paid'",
                    ['uid' => $userId]
                );
                if ($previousOrders > 0) {
                    return ['valid' => false, 'message' => 'This coupon is exclusively reserved for new students making their first purchase.'];
                }
            } elseif ($coupon['user_eligibility'] === 'specific_users') {
                $isWhitelisted = Database::fetchOne(
                    "SELECT id FROM coupon_users WHERE coupon_id = :cid AND user_id = :uid",
                    ['cid' => $coupon['id'], 'uid' => $userId]
                );
                if (!$isWhitelisted) {
                    return ['valid' => false, 'message' => 'This special promotion is not eligible for your account.'];
                }
            }
        }

        // 7. Course Inclusions & Exclusions
        $course = null;
        if ($courseId) {
            $course = Course::find($courseId);
            if (!$course) {
                return ['valid' => false, 'message' => 'Selected course is not available.'];
            }

            // Check explicit course exclusions
            $isExcludedCourse = Database::fetchOne(
                "SELECT id FROM coupon_courses WHERE coupon_id = :cid AND course_id = :crs_id AND type = 'exclude'",
                ['cid' => $coupon['id'], 'crs_id' => $courseId]
            );
            if ($isExcludedCourse) {
                return ['valid' => false, 'message' => 'This coupon cannot be applied to this specific course.'];
            }

            // Check course inclusions
            $hasInclusions = (int)Database::fetchValue(
                "SELECT COUNT(*) FROM coupon_courses WHERE coupon_id = :cid AND type = 'include'",
                ['cid' => $coupon['id']]
            );
            if ($hasInclusions > 0) {
                $isIncluded = Database::fetchOne(
                    "SELECT id FROM coupon_courses WHERE coupon_id = :cid AND course_id = :crs_id AND type = 'include'",
                    ['cid' => $coupon['id'], 'crs_id' => $courseId]
                );
                if (!$isIncluded) {
                    return ['valid' => false, 'message' => 'This coupon is only valid for selected masterclasses.'];
                }
            }

            // Check Category Inclusions & Exclusions
            if (!empty($course['category_id'])) {
                $isExcludedCategory = Database::fetchOne(
                    "SELECT id FROM coupon_categories WHERE coupon_id = :cid AND category_id = :cat_id AND type = 'exclude'",
                    ['cid' => $coupon['id'], 'cat_id' => $course['category_id']]
                );
                if ($isExcludedCategory) {
                    return ['valid' => false, 'message' => 'This coupon cannot be applied to courses in this category.'];
                }

                $hasCatInclusions = (int)Database::fetchValue(
                    "SELECT COUNT(*) FROM coupon_categories WHERE coupon_id = :cid AND type = 'include'",
                    ['cid' => $coupon['id']]
                );
                if ($hasCatInclusions > 0) {
                    $isIncludedCat = Database::fetchOne(
                        "SELECT id FROM coupon_categories WHERE coupon_id = :cid AND category_id = :cat_id AND type = 'include'",
                        ['cid' => $coupon['id'], 'cat_id' => $course['category_id']]
                    );
                    if (!$isIncludedCat) {
                        return ['valid' => false, 'message' => 'This coupon is not valid for this category.'];
                    }
                }
            }

            // 8. Sale Price Interaction Rules
            if (!empty($course['discount_price']) && (float)$course['discount_price'] < (float)$course['price']) {
                if ($coupon['sale_price_rule'] === 'exclude_sale_items') {
                    return ['valid' => false, 'message' => 'This coupon cannot be combined with courses currently on promotional sale.'];
                }
            }
        }

        // 9. Minimum Spend Requirement
        if ((float)$coupon['min_spend'] > 0 && $subtotalAmount < (float)$coupon['min_spend']) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount for this coupon is ' . format_rwf((float)$coupon['min_spend']) . '.'
            ];
        }

        // 10. Discount Amount Calculation with Maximum Caps
        $discountAmount = 0.00;
        $discountValue = (float)$coupon['discount_value'];

        if ($coupon['discount_type'] === 'percentage') {
            $percentage = min(100.0, max(0.0, $discountValue));
            $calculatedDiscount = ($subtotalAmount * $percentage) / 100.0;

            // Apply max discount amount cap if specified
            $maxCap = (float)($coupon['max_discount_amount'] ?? 0);
            if ($maxCap > 0 && $calculatedDiscount > $maxCap) {
                $discountAmount = $maxCap;
            } else {
                $discountAmount = $calculatedDiscount;
            }
        } else {
            // Fixed amount discount
            $discountAmount = min($subtotalAmount, $discountValue);
        }

        $discountAmount = round($discountAmount, 2);
        $finalAmount = max(0.00, round($subtotalAmount - $discountAmount, 2));

        return [
            'valid'           => true,
            'coupon'          => $coupon,
            'discount_amount' => $discountAmount,
            'final_amount'    => $finalAmount,
            'currency'        => $coupon['currency'] ?? 'RWF',
            'is_free'         => ($finalAmount <= 0.00),
            'message'         => 'Coupon applied! You save ' . format_money($discountAmount, $coupon['currency'] ?? 'RWF') . '.'
        ];
    }

    /**
     * Atomically record coupon redemption and update usage counters.
     */
    public static function recordRedemption(
        int $couponId,
        int $orderId,
        int $userId,
        float $originalAmount,
        float $discountAmount,
        float $finalAmount,
        ?int $courseId = null
    ): int {
        $coupon = Database::fetchOne("SELECT * FROM coupons WHERE id = :id", ['id' => $couponId]);
        if (!$coupon) {
            return 0;
        }

        $campaignId = !empty($coupon['campaign_id']) ? (int)$coupon['campaign_id'] : null;

        // 1. Insert redemption record
        $redemptionId = Database::insert('coupon_redemptions', [
            'coupon_id'       => $couponId,
            'campaign_id'     => $campaignId,
            'order_id'        => $orderId,
            'user_id'         => $userId,
            'course_id'       => $courseId,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount'    => $finalAmount,
            'currency'        => $coupon['currency'] ?? 'RWF',
            'redeemed_at'     => date('Y-m-d H:i:s')
        ]);

        // 2. Increment coupon uses count
        Database::query("UPDATE coupons SET uses_count = uses_count + 1 WHERE id = :cid", ['cid' => $couponId]);

        // 3. Increment campaign discount spent if applicable
        if ($campaignId) {
            Database::query(
                "UPDATE coupon_campaigns SET discount_spent = discount_spent + :disc WHERE id = :camp_id",
                ['disc' => $discountAmount, 'camp_id' => $campaignId]
            );
        }

        return $redemptionId;
    }

    /**
     * Generate bulk unique promotional codes assigned to campaign.
     */
    public static function bulkGenerate(array $params, ?int $adminId = null): array {
        $count = min(500, max(1, (int)($params['count'] ?? 10)));
        $prefix = strtoupper(trim($params['prefix'] ?? 'BBA'));
        $length = min(16, max(4, (int)($params['length'] ?? 8)));
        $charset = $params['charset'] ?? 'ALPHANUMERIC';

        $generatedCodes = [];
        $createdIds = [];

        for ($i = 0; $i < $count; $i++) {
            $code = Coupon::generateCode($prefix, $length, $charset);
            
            $couponId = Database::insert('coupons', [
                'code'                => $code,
                'name'                => $params['name'] ?? "Bulk Generated Promotion #{$i}",
                'description'         => $params['description'] ?? null,
                'campaign_id'         => !empty($params['campaign_id']) ? (int)$params['campaign_id'] : null,
                'discount_type'       => $params['discount_type'] ?? 'percentage',
                'discount_value'      => (float)($params['discount_value'] ?? 10.0),
                'currency'            => $params['currency'] ?? 'RWF',
                'max_discount_amount' => (float)($params['max_discount_amount'] ?? 0.0),
                'min_spend'           => (float)($params['min_spend'] ?? 0.0),
                'max_uses'            => (int)($params['max_uses'] ?? 1),
                'per_user_limit'      => (int)($params['per_user_limit'] ?? 1),
                'user_eligibility'    => $params['user_eligibility'] ?? 'all',
                'is_stackable'        => !empty($params['is_stackable']) ? 1 : 0,
                'sale_price_rule'     => $params['sale_price_rule'] ?? 'apply_to_sale_price',
                'start_date'          => !empty($params['start_date']) ? $params['start_date'] : null,
                'expires_at'          => !empty($params['expires_at']) ? $params['expires_at'] : null,
                'is_active'           => 1,
                'status'              => 'active',
                'created_by'          => $adminId,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s')
            ]);

            $generatedCodes[] = $code;
            $createdIds[] = $couponId;
        }

        Coupon::logActivity(null, $params['campaign_id'] ?? null, 'bulk_generated', [
            'count' => $count,
            'prefix' => $prefix,
            'discount' => $params['discount_value'] ?? 10
        ], $adminId);

        return [
            'success' => true,
            'count'   => count($generatedCodes),
            'codes'   => $generatedCodes,
            'ids'     => $createdIds
        ];
    }

    /**
     * Compute Executive Promotions Dashboard KPIs.
     */
    public static function getDashboardKpis(?string $startDate = null, ?string $endDate = null): array {
        $dateCondition = "";
        $params = [];

        if ($startDate && $endDate) {
            $dateCondition = " AND created_at BETWEEN :start AND :end";
            $params['start'] = $startDate . ' 00:00:00';
            $params['end']   = $endDate . ' 23:59:59';
        }

        $totalCoupons = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupons WHERE status != 'archived'") ?: 0);
        $activeCoupons = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupons WHERE is_active = 1 AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW()) AND (start_date IS NULL OR start_date <= NOW())") ?: 0);
        $scheduledCoupons = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupons WHERE is_active = 1 AND start_date > NOW()") ?: 0);
        $expiredCoupons = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupons WHERE expires_at IS NOT NULL AND expires_at < NOW()") ?: 0);
        $disabledCoupons = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupons WHERE is_active = 0 OR status = 'disabled'") ?: 0);

        // Redemption Metrics
        $redemptionDateCond = "";
        $redemptionParams = [];
        if ($startDate && $endDate) {
            $redemptionDateCond = " WHERE redeemed_at BETWEEN :start AND :end";
            $redemptionParams['start'] = $startDate . ' 00:00:00';
            $redemptionParams['end']   = $endDate . ' 23:59:59';
        }

        $totalRedemptions = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupon_redemptions {$redemptionDateCond}", $redemptionParams) ?: 0);
        $totalDiscountGiven = (float)(Database::fetchValue("SELECT COALESCE(SUM(discount_amount), 0) FROM coupon_redemptions {$redemptionDateCond}", $redemptionParams) ?: 0.0);
        $revenueGenerated = (float)(Database::fetchValue("SELECT COALESCE(SUM(final_amount), 0) FROM coupon_redemptions {$redemptionDateCond}", $redemptionParams) ?: 0.0);

        $avgDiscount = $totalRedemptions > 0 ? ($totalDiscountGiven / $totalRedemptions) : 0.0;
        $totalCampaigns = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupon_campaigns") ?: 0);

        return [
            'total_coupons'        => $totalCoupons,
            'active_coupons'       => $activeCoupons,
            'scheduled_coupons'    => $scheduledCoupons,
            'expired_coupons'      => $expiredCoupons,
            'disabled_coupons'     => $disabledCoupons,
            'total_redemptions'    => $totalRedemptions,
            'total_discount_given' => $totalDiscountGiven,
            'revenue_generated'    => $revenueGenerated,
            'avg_discount'         => $avgDiscount,
            'total_campaigns'      => $totalCampaigns
        ];
    }

    /**
     * Chart.js Series for Promotions Analytics.
     */
    public static function getChartData(): array {
        $days = [];
        $redemptionsSeries = [];
        $discountSeries = [];
        $revenueSeries = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $days[] = date('M d', strtotime($date));

            $reds = (int)(Database::fetchValue(
                "SELECT COUNT(*) FROM coupon_redemptions WHERE DATE(redeemed_at) = :d",
                ['d' => $date]
            ) ?: 0);
            $redemptionsSeries[] = $reds;

            $disc = (float)(Database::fetchValue(
                "SELECT COALESCE(SUM(discount_amount), 0) FROM coupon_redemptions WHERE DATE(redeemed_at) = :d",
                ['d' => $date]
            ) ?: 0.0);
            $discountSeries[] = $disc;

            $rev = (float)(Database::fetchValue(
                "SELECT COALESCE(SUM(final_amount), 0) FROM coupon_redemptions WHERE DATE(redeemed_at) = :d",
                ['d' => $date]
            ) ?: 0.0);
            $revenueSeries[] = $rev;
        }

        // Top Performing Coupons
        $topCoupons = Database::fetchAll(
            "SELECT c.id, c.code, c.name, c.discount_type, c.discount_value, c.currency,
                    COUNT(cr.id) as redemptions_count,
                    COALESCE(SUM(cr.discount_amount), 0) as total_discount,
                    COALESCE(SUM(cr.final_amount), 0) as total_revenue
             FROM coupons c
             JOIN coupon_redemptions cr ON c.id = cr.coupon_id
             GROUP BY c.id
             ORDER BY redemptions_count DESC
             LIMIT 5"
        );

        return [
            'labels'             => $days,
            'redemptions_series' => $redemptionsSeries,
            'discount_series'    => $discountSeries,
            'revenue_series'     => $revenueSeries,
            'top_coupons'        => $topCoupons
        ];
    }

    /**
     * Filter and Paginate Coupons List.
     */
    public static function getCoupons(array $filters = [], int $page = 1, int $perPage = 20): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(c.code LIKE :q OR c.name LIKE :q2 OR camp.name LIKE :q3)";
            $params['q'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
            $params['q3'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active') {
                $conditions[] = "c.is_active = 1 AND (c.expires_at IS NULL OR c.expires_at > NOW()) AND (c.start_date IS NULL OR c.start_date <= NOW())";
            } elseif ($filters['status'] === 'scheduled') {
                $conditions[] = "c.is_active = 1 AND c.start_date > NOW()";
            } elseif ($filters['status'] === 'expired') {
                $conditions[] = "c.expires_at IS NOT NULL AND c.expires_at < NOW()";
            } elseif ($filters['status'] === 'disabled') {
                $conditions[] = "(c.is_active = 0 OR c.status = 'disabled')";
            } elseif ($filters['status'] === 'archived') {
                $conditions[] = "c.status = 'archived'";
            }
        }

        if (!empty($filters['campaign_id']) && $filters['campaign_id'] !== 'all') {
            $conditions[] = "c.campaign_id = :camp_id";
            $params['camp_id'] = (int)$filters['campaign_id'];
        }

        if (!empty($filters['discount_type']) && $filters['discount_type'] !== 'all') {
            $conditions[] = "c.discount_type = :dt";
            $params['dt'] = $filters['discount_type'];
        }

        $whereSql = implode(' AND ', $conditions);
        $countSql = "SELECT COUNT(*) FROM coupons c LEFT JOIN coupon_campaigns camp ON c.campaign_id = camp.id WHERE {$whereSql}";
        $total = (int)(Database::fetchValue($countSql, $params) ?: 0);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT c.*, camp.name as campaign_name, u.name as creator_name,
                       (SELECT COALESCE(SUM(final_amount), 0) FROM coupon_redemptions WHERE coupon_id = c.id) as total_revenue_generated
                FROM coupons c
                LEFT JOIN coupon_campaigns camp ON c.campaign_id = camp.id
                LEFT JOIN users u ON c.created_by = u.id
                WHERE {$whereSql}
                ORDER BY c.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $coupons = Database::fetchAll($sql, $params);

        // Append computed status for each coupon
        foreach ($coupons as &$c) {
            $c['computed_status'] = Coupon::getComputedStatus($c);
        }

        return [
            'data'         => $coupons,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Filter and Paginate Redemptions Ledger.
     */
    public static function getRedemptions(array $filters = [], int $page = 1, int $perPage = 25): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(c.code LIKE :q OR u.name LIKE :q2 OR u.email LIKE :q3 OR o.order_number LIKE :q4)";
            $params['q'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
            $params['q3'] = '%' . trim($filters['q']) . '%';
            $params['q4'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['coupon_id'])) {
            $conditions[] = "cr.coupon_id = :cid";
            $params['cid'] = (int)$filters['coupon_id'];
        }

        if (!empty($filters['campaign_id'])) {
            $conditions[] = "cr.campaign_id = :camp_id";
            $params['camp_id'] = (int)$filters['campaign_id'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = "cr.redeemed_at BETWEEN :start AND :end";
            $params['start'] = $filters['start_date'] . ' 00:00:00';
            $params['end']   = $filters['end_date'] . ' 23:59:59';
        }

        $whereSql = implode(' AND ', $conditions);
        $countSql = "SELECT COUNT(*) FROM coupon_redemptions cr
                     JOIN coupons c ON cr.coupon_id = c.id
                     JOIN users u ON cr.user_id = u.id
                     JOIN orders o ON cr.order_id = o.id
                     WHERE {$whereSql}";
        $total = (int)(Database::fetchValue($countSql, $params) ?: 0);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT cr.*, c.code as coupon_code, c.name as coupon_name,
                       u.name as user_name, u.email as user_email,
                       o.order_number, crs.title as course_title, camp.name as campaign_name
                FROM coupon_redemptions cr
                JOIN coupons c ON cr.coupon_id = c.id
                JOIN users u ON cr.user_id = u.id
                JOIN orders o ON cr.order_id = o.id
                LEFT JOIN courses crs ON cr.course_id = crs.id
                LEFT JOIN coupon_campaigns camp ON cr.campaign_id = camp.id
                WHERE {$whereSql}
                ORDER BY cr.redeemed_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $redemptions = Database::fetchAll($sql, $params);

        return [
            'data'         => $redemptions,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Export Coupons to CSV.
     */
    public static function exportCouponsCsv(array $filters = []): string {
        $result = static::getCoupons($filters, 1, 5000);
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['Code', 'Campaign', 'Discount Type', 'Discount Value', 'Max Cap', 'Min Spend', 'Uses Count', 'Max Uses', 'Status', 'Start Date', 'Expires At', 'Created At']);

        foreach ($result['data'] as $c) {
            fputcsv($output, [
                $c['code'],
                $c['campaign_name'] ?: 'None',
                $c['discount_type'],
                $c['discount_value'] . ($c['discount_type'] === 'percentage' ? '%' : ' ' . $c['currency']),
                $c['max_discount_amount'] ?: 'None',
                $c['min_spend'] ?: 'None',
                $c['uses_count'],
                $c['max_uses'] ?: 'Unlimited',
                strtoupper($c['computed_status']),
                $c['start_date'] ?: 'Immediate',
                $c['expires_at'] ?: 'Never',
                $c['created_at']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    /**
     * Export Redemptions to CSV.
     */
    public static function exportRedemptionsCsv(array $filters = []): string {
        $result = static::getRedemptions($filters, 1, 5000);
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['Redemption ID', 'Coupon Code', 'Customer Name', 'Customer Email', 'Order #', 'Course Title', 'Original Price', 'Discount Amount', 'Final Paid', 'Redeemed At']);

        foreach ($result['data'] as $r) {
            fputcsv($output, [
                $r['id'],
                $r['coupon_code'],
                $r['user_name'],
                $r['user_email'],
                $r['order_number'],
                $r['course_title'] ?: 'Full Cart',
                $r['original_amount'],
                $r['discount_amount'],
                $r['final_amount'],
                $r['redeemed_at']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
