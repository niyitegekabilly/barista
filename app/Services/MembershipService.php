<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\MembershipRenewal;
use App\Models\Order;
use App\Models\Course;
use App\Models\Notification;
use App\Models\AuditLog;

class MembershipService {

    /**
     * Create or extend a subscription from a completed order.
     */
    public static function createSubscriptionFromOrder(
        int $orderId,
        int $userId,
        int $planId,
        string $paymentMethod = 'momo'
    ): array {
        $plan = MembershipPlan::findWithRelations($planId);
        if (!$plan) {
            return ['success' => false, 'message' => 'Membership plan not found.'];
        }

        $order = Order::findWithRelations($orderId);
        $orderAmount = $order ? (float)$order['final_amount'] : (float)$plan['price'];
        $currency = $order['currency'] ?? ($plan['currency'] ?? 'RWF');

        $startDate = date('Y-m-d');
        $interval = $plan['billing_interval'];
        $endDate = match ($interval) {
            'year' => date('Y-m-d', strtotime('+1 year')),
            'lifetime' => date('Y-m-d', strtotime('+100 years')),
            default => date('Y-m-d', strtotime('+1 month'))
        };

        $trialDays = (int)$plan['trial_period_days'];
        $status = 'active';
        $trialEndsAt = null;

        if ($trialDays > 0) {
            $status = 'trialing';
            $trialEndsAt = date('Y-m-d H:i:s', strtotime("+{$trialDays} days"));
        }

        // Check if user already has an active subscription for this plan (renewal/extension)
        $existing = Database::fetchOne(
            "SELECT * FROM memberships WHERE user_id = :uid AND plan_id = :pid AND status IN ('active', 'trialing', 'grace_period') LIMIT 1",
            ['uid' => $userId, 'pid' => $planId]
        );

        if ($existing) {
            // Extend existing subscription
            $newEndDate = date('Y-m-d', strtotime($existing['end_date'] . " +1 {$interval}"));
            Database::update('memberships', [
                'end_date'          => $newEndDate,
                'renewal_date'      => $newEndDate,
                'status'            => 'active',
                'order_id'          => $orderId,
                'payment_reference' => $order['latest_payment']['transaction_reference'] ?? $existing['payment_reference'],
                'updated_at'        => date('Y-m-d H:i:s')
            ], ['id' => $existing['id']]);

            $membershipId = (int)$existing['id'];
            $subscriptionNumber = $existing['subscription_number'];

            Membership::logActivity($membershipId, 'renewed', [
                'order_number' => $order['order_number'] ?? '',
                'extended_to'  => $newEndDate
            ], $userId);
        } else {
            // Create new subscription record
            $subscriptionNumber = Membership::generateSubscriptionNumber();

            $membershipId = Database::insert('memberships', [
                'subscription_number' => $subscriptionNumber,
                'user_id'             => $userId,
                'plan_id'             => $planId,
                'order_id'            => $orderId,
                'status'              => $status,
                'auto_renew'          => 1,
                'start_date'          => $startDate,
                'end_date'            => $endDate,
                'renewal_date'        => $endDate,
                'trial_ends_at'       => $trialEndsAt,
                'payment_reference'   => $order['latest_payment']['transaction_reference'] ?? 'DIRECT-ORDER',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s')
            ]);

            Membership::logActivity($membershipId, 'created', [
                'plan_name'    => $plan['name'],
                'order_number' => $order['order_number'] ?? '',
                'billing'      => $interval
            ], $userId);
        }

        // Record Renewal History Log
        MembershipRenewal::record([
            'membership_id' => $membershipId,
            'order_id'      => $orderId,
            'amount'        => $orderAmount,
            'currency'      => $currency,
            'status'        => 'success',
            'billing_date'  => $startDate,
            'period_start'  => $startDate,
            'period_end'    => $endDate,
            'notes'         => 'Subscription payment verified for plan ' . $plan['name']
        ]);

        // Auto-grant enrollments for included courses in the plan
        if ($plan['course_access_type'] === 'specific_courses') {
            foreach ($plan['courses'] as $crs) {
                static::ensureEnrollment($userId, (int)$crs['id']);
            }
        } elseif ($plan['course_access_type'] === 'all_courses') {
            // Student gets full access to all courses dynamically via canUserAccessCourse()
        }

        // Send Student Welcome Notification
        Notification::send(
            $userId,
            'Welcome to Beyond Barista ' . $plan['name'] . '!',
            'Your subscription has been activated successfully with full access until ' . date('M d, Y', strtotime($endDate)) . '.',
            url('student/subscription')
        );

        AuditLog::log('subscription_activated', 'membership', $membershipId, [
            'subscription_number' => $subscriptionNumber,
            'plan' => $plan['name'],
            'user_id' => $userId
        ]);

        return [
            'success'             => true,
            'membership_id'       => $membershipId,
            'subscription_number' => $subscriptionNumber,
            'plan_name'           => $plan['name'],
            'end_date'            => $endDate,
            'message'             => 'Subscription activated successfully.'
        ];
    }

    /**
     * Dynamic Access Control & Course Gating Resolver.
     */
    public static function canUserAccessCourse(int $userId, int $courseId): bool {
        // 1. Direct course purchase / active enrollment
        $directEnrollment = Database::fetchOne(
            "SELECT id FROM enrollments WHERE user_id = :uid AND course_id = :cid AND status IN ('active', 'completed') LIMIT 1",
            ['uid' => $userId, 'cid' => $courseId]
        );
        if ($directEnrollment) {
            return true;
        }

        // 2. Query active subscriptions
        $now = date('Y-m-d');
        $activeSubs = Database::fetchAll(
            "SELECT m.*, p.course_access_type, p.has_certificate_access
             FROM memberships m
             JOIN membership_plans p ON m.plan_id = p.id
             WHERE m.user_id = :uid AND m.status IN ('active', 'trialing', 'grace_period') AND m.end_date >= :today",
            ['uid' => $userId, 'today' => $now]
        );

        if (empty($activeSubs)) {
            return false;
        }

        $course = Course::find($courseId);
        if (!$course) return false;

        foreach ($activeSubs as $sub) {
            if ($sub['course_access_type'] === 'all_courses') {
                return true;
            }

            if ($sub['course_access_type'] === 'specific_courses') {
                $isMapped = Database::fetchOne(
                    "SELECT id FROM membership_plan_courses WHERE plan_id = :pid AND course_id = :cid",
                    ['pid' => $sub['plan_id'], 'cid' => $courseId]
                );
                if ($isMapped) return true;
            }

            if ($sub['course_access_type'] === 'specific_categories' && !empty($course['category_id'])) {
                $isCatMapped = Database::fetchOne(
                    "SELECT id FROM membership_plan_categories WHERE plan_id = :pid AND category_id = :cat_id",
                    ['pid' => $sub['plan_id'], 'cat_id' => $course['category_id']]
                );
                if ($isCatMapped) return true;
            }
        }

        return false;
    }

    /**
     * Helper to ensure active enrollment record exists.
     */
    private static function ensureEnrollment(int $userId, int $courseId): void {
        $existing = Database::fetchOne("SELECT id FROM enrollments WHERE user_id = :uid AND course_id = :cid", ['uid' => $userId, 'cid' => $courseId]);
        if ($existing) {
            Database::update('enrollments', ['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')], ['id' => $existing['id']]);
        } else {
            Database::insert('enrollments', [
                'user_id'          => $userId,
                'course_id'        => $courseId,
                'status'           => 'active',
                'progress_percent' => 0,
                'enrolled_at'      => date('Y-m-d H:i:s'),
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Extend subscription validity period manually by admin.
     */
    public static function extendSubscription(int $membershipId, int $days, string $reason = '', ?int $adminId = null): array {
        $sub = Membership::find($membershipId);
        if (!$sub) {
            return ['success' => false, 'message' => 'Subscription not found.'];
        }

        $baseDate = strtotime($sub['end_date']) > time() ? $sub['end_date'] : date('Y-m-d');
        $newEndDate = date('Y-m-d', strtotime("{$baseDate} +{$days} days"));

        Database::update('memberships', [
            'end_date'     => $newEndDate,
            'renewal_date' => $newEndDate,
            'status'       => 'active',
            'updated_at'   => date('Y-m-d H:i:s')
        ], ['id' => $membershipId]);

        Membership::logActivity($membershipId, 'extended', [
            'days_added' => $days,
            'new_end_date' => $newEndDate,
            'reason' => $reason
        ], $adminId);

        Notification::send(
            (int)$sub['user_id'],
            'Subscription Extended',
            "Your membership subscription has been extended by {$days} days until " . date('M d, Y', strtotime($newEndDate)) . '.',
            url('student/subscription')
        );

        return [
            'success'      => true,
            'new_end_date' => $newEndDate,
            'message'      => "Subscription extended by {$days} days until " . date('M d, Y', strtotime($newEndDate)) . "."
        ];
    }

    /**
     * Cancel Subscription.
     */
    public static function cancelSubscription(int $membershipId, string $reason = '', bool $immediate = false, ?int $userId = null): array {
        $sub = Membership::find($membershipId);
        if (!$sub) {
            return ['success' => false, 'message' => 'Subscription not found.'];
        }

        if ($immediate) {
            Database::update('memberships', [
                'status'              => 'cancelled',
                'auto_renew'          => 0,
                'cancelled_at'        => date('Y-m-d H:i:s'),
                'cancellation_reason' => trim($reason),
                'updated_at'          => date('Y-m-d H:i:s')
            ], ['id' => $membershipId]);
        } else {
            Database::update('memberships', [
                'auto_renew'          => 0,
                'cancellation_reason' => trim($reason),
                'updated_at'          => date('Y-m-d H:i:s')
            ], ['id' => $membershipId]);
        }

        Membership::logActivity($membershipId, 'cancelled', [
            'immediate' => $immediate,
            'reason'    => $reason
        ], $userId);

        return [
            'success' => true,
            'message' => $immediate ? 'Subscription cancelled immediately.' : 'Auto-renewal turned off. Access remains active until period end.'
        ];
    }

    /**
     * Compute Executive MRR & Subscription Analytics KPIs.
     */
    public static function getDashboardKpis(?string $startDate = null, ?string $endDate = null): array {
        $activeSubscribers = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT user_id) FROM memberships WHERE status IN ('active', 'grace_period')"
        ) ?: 0);

        $trialingSubscribers = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT user_id) FROM memberships WHERE status = 'trialing'"
        ) ?: 0);

        $expiredSubscribers = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT user_id) FROM memberships WHERE status = 'expired'"
        ) ?: 0);

        $cancelledSubscribers = (int)(Database::fetchValue(
            "SELECT COUNT(DISTINCT user_id) FROM memberships WHERE status = 'cancelled'"
        ) ?: 0);

        $totalSubscriptions = (int)(Database::fetchValue("SELECT COUNT(*) FROM memberships") ?: 0);

        // Compute MRR & ARR
        $mrrMonthly = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(p.price), 0) FROM memberships m JOIN membership_plans p ON m.plan_id = p.id WHERE m.status IN ('active', 'grace_period') AND p.billing_interval = 'month'"
        ) ?: 0.0);

        $mrrYearly = (float)(Database::fetchValue(
            "SELECT COALESCE(SUM(p.price / 12), 0) FROM memberships m JOIN membership_plans p ON m.plan_id = p.id WHERE m.status IN ('active', 'grace_period') AND p.billing_interval = 'year'"
        ) ?: 0.0);

        $mrr = round($mrrMonthly + $mrrYearly, 2);
        $arr = round($mrr * 12.0, 2);

        // ARPU (Average Revenue Per User)
        $arpu = $activeSubscribers > 0 ? round($mrr / $activeSubscribers, 2) : 0.0;

        // Churn Rate
        $totalEver = $activeSubscribers + $cancelledSubscribers;
        $churnRate = $totalEver > 0 ? round(($cancelledSubscribers / $totalEver) * 100.0, 1) : 0.0;

        $totalPlans = (int)(Database::fetchValue("SELECT COUNT(*) FROM membership_plans WHERE status != 'archived'") ?: 0);

        return [
            'active_subscribers'    => $activeSubscribers,
            'trialing_subscribers'  => $trialingSubscribers,
            'expired_subscribers'   => $expiredSubscribers,
            'cancelled_subscribers' => $cancelledSubscribers,
            'total_subscriptions'  => $totalSubscriptions,
            'mrr'                   => $mrr,
            'arr'                   => $arr,
            'arpu'                  => $arpu,
            'churn_rate'            => $churnRate,
            'total_plans'           => $totalPlans
        ];
    }

    /**
     * Chart.js Series for MRR and Subscriber Growth.
     */
    public static function getChartData(): array {
        $days = [];
        $subscribersGrowth = [];
        $revenueStream = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $days[] = date('M d', strtotime($date));

            $subs = (int)(Database::fetchValue(
                "SELECT COUNT(*) FROM memberships WHERE DATE(created_at) <= :d AND status IN ('active', 'trialing', 'grace_period')",
                ['d' => $date]
            ) ?: 0);
            $subscribersGrowth[] = $subs;

            $rev = (float)(Database::fetchValue(
                "SELECT COALESCE(SUM(amount), 0) FROM membership_renewals WHERE DATE(created_at) = :d AND status = 'success'",
                ['d' => $date]
            ) ?: 0.0);
            $revenueStream[] = $rev;
        }

        // Plan distribution
        $planBreakdown = Database::fetchAll(
            "SELECT p.name, p.billing_interval, COUNT(m.id) as subscriber_count, COALESCE(SUM(p.price), 0) as total_value
             FROM membership_plans p
             LEFT JOIN memberships m ON p.id = m.plan_id AND m.status IN ('active', 'trialing', 'grace_period')
             WHERE p.status != 'archived'
             GROUP BY p.id
             ORDER BY subscriber_count DESC"
        );

        return [
            'labels'             => $days,
            'subscribers_series' => $subscribersGrowth,
            'revenue_series'     => $revenueStream,
            'plan_breakdown'     => $planBreakdown
        ];
    }

    /**
     * Filter and Paginate Subscriptions List.
     */
    public static function getSubscriptions(array $filters = [], int $page = 1, int $perPage = 20): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(m.subscription_number LIKE :q OR u.name LIKE :q2 OR u.email LIKE :q3 OR p.name LIKE :q4)";
            $params['q'] = '%' . trim($filters['q']) . '%';
            $params['q2'] = '%' . trim($filters['q']) . '%';
            $params['q3'] = '%' . trim($filters['q']) . '%';
            $params['q4'] = '%' . trim($filters['q']) . '%';
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "m.status = :st";
            $params['st'] = $filters['status'];
        }

        if (!empty($filters['plan_id']) && $filters['plan_id'] !== 'all') {
            $conditions[] = "m.plan_id = :pid";
            $params['pid'] = (int)$filters['plan_id'];
        }

        $whereSql = implode(' AND ', $conditions);
        $countSql = "SELECT COUNT(*) FROM memberships m JOIN users u ON m.user_id = u.id JOIN membership_plans p ON m.plan_id = p.id WHERE {$whereSql}";
        $total = (int)(Database::fetchValue($countSql, $params) ?: 0);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT m.*, u.name as user_name, u.email as user_email, p.name as plan_name, p.billing_interval, p.price as plan_price
                FROM memberships m
                JOIN users u ON m.user_id = u.id
                JOIN membership_plans p ON m.plan_id = p.id
                WHERE {$whereSql}
                ORDER BY m.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $subs = Database::fetchAll($sql, $params);
        foreach ($subs as &$s) {
            $s['days_remaining'] = Membership::calculateDaysRemaining($s['end_date']);
        }

        return [
            'data'         => $subs,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => max(1, (int)ceil($total / $perPage))
        ];
    }

    /**
     * Export Subscriptions to CSV.
     */
    public static function exportSubscriptionsCsv(array $filters = []): string {
        $result = static::getSubscriptions($filters, 1, 5000);
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['Subscription #', 'Student Name', 'Student Email', 'Plan Name', 'Billing Interval', 'Status', 'Auto Renew', 'Start Date', 'End Date', 'Days Left', 'Created At']);

        foreach ($result['data'] as $s) {
            fputcsv($output, [
                $s['subscription_number'],
                $s['user_name'],
                $s['user_email'],
                $s['plan_name'],
                strtoupper($s['billing_interval']),
                strtoupper($s['status']),
                $s['auto_renew'] ? 'YES' : 'NO',
                $s['start_date'],
                $s['end_date'],
                $s['days_remaining'],
                $s['created_at']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
