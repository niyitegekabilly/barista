<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Membership extends Model {
    protected static string $table = 'memberships';

    public static function findBySubscriptionNumber(string $number): ?array {
        return Database::fetchOne("SELECT * FROM memberships WHERE subscription_number = :num LIMIT 1", ['num' => trim($number)]);
    }

    public static function findWithRelations(int $id): ?array {
        $sub = Database::fetchOne(
            "SELECT m.*, u.name as user_name, u.email as user_email, u.avatar as user_avatar,
                    p.name as plan_name, p.slug as plan_slug, p.price as plan_price, p.billing_interval,
                    p.course_access_type, p.has_certificate_access, p.has_live_workshops
             FROM memberships m
             JOIN users u ON m.user_id = u.id
             JOIN membership_plans p ON m.plan_id = p.id
             WHERE m.id = :id LIMIT 1",
            ['id' => $id]
        );
        if (!$sub) return null;

        $sub['order'] = !empty($sub['order_id']) ? Order::findWithRelations((int)$sub['order_id']) : null;
        $sub['renewals'] = Database::fetchAll("SELECT * FROM membership_renewals WHERE membership_id = :id ORDER BY created_at DESC", ['id' => $id]);
        $sub['activity_logs'] = Database::fetchAll(
            "SELECT mal.*, u.name as admin_name FROM membership_activity_logs mal LEFT JOIN users u ON mal.user_id = u.id WHERE mal.membership_id = :id ORDER BY mal.created_at DESC",
            ['id' => $id]
        );

        $sub['days_remaining'] = static::calculateDaysRemaining($sub['end_date']);
        $sub['is_active_access'] = in_array($sub['status'], ['active', 'trialing', 'grace_period']) && strtotime($sub['end_date']) >= strtotime('today');

        return $sub;
    }

    public static function generateSubscriptionNumber(): string {
        $prefix = 'BBA-SUB-' . date('Ym');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $num = $prefix . '-' . $random;

        while (Database::fetchOne("SELECT id FROM memberships WHERE subscription_number = :num", ['num' => $num])) {
            $random = strtoupper(bin2hex(random_bytes(2)));
            $num = $prefix . '-' . $random;
        }

        return $num;
    }

    public static function calculateDaysRemaining(string $endDate): int {
        $now = time();
        $end = strtotime($endDate . ' 23:59:59');
        if ($end < $now) return 0;

        return (int)ceil(($end - $now) / 86400);
    }

    public static function logActivity(int $membershipId, string $action, array $details = [], ?int $userId = null): void {
        Database::insert('membership_activity_logs', [
            'membership_id' => $membershipId,
            'user_id'       => $userId ?: auth_id(),
            'action'        => $action,
            'details'       => json_encode($details),
            'created_at'    => date('Y-m-d H:i:s')
        ]);
    }
}
