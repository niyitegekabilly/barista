<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class MembershipPlan extends Model {
    protected static string $table = 'membership_plans';

    public static function getActive(): array {
        return Database::fetchAll("SELECT * FROM membership_plans WHERE is_active = 1 AND status != 'archived' ORDER BY sort_order ASC, price ASC");
    }

    public static function findBySlug(string $slug): ?array {
        return Database::fetchOne("SELECT * FROM membership_plans WHERE slug = :s LIMIT 1", ['s' => trim($slug)]);
    }

    public static function findWithRelations(int $id): ?array {
        $plan = Database::fetchOne("SELECT * FROM membership_plans WHERE id = :id LIMIT 1", ['id' => $id]);
        if (!$plan) return null;

        $plan['courses'] = Database::fetchAll(
            "SELECT c.id, c.title, c.slug, c.price FROM membership_plan_courses mpc JOIN courses c ON mpc.course_id = c.id WHERE mpc.plan_id = :id",
            ['id' => $id]
        );

        $plan['categories'] = Database::fetchAll(
            "SELECT cat.id, cat.name, cat.slug FROM membership_plan_categories mpc JOIN categories cat ON mpc.category_id = cat.id WHERE mpc.plan_id = :id",
            ['id' => $id]
        );

        $plan['active_subscribers_count'] = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM memberships WHERE plan_id = :id AND status IN ('active', 'trialing', 'grace_period')",
            ['id' => $id]
        ) ?: 0);

        $plan['mrr_contribution'] = static::calculateMrr($plan['price'], $plan['billing_interval'], $plan['active_subscribers_count']);

        return $plan;
    }

    public static function calculateMrr(float $price, string $interval, int $subscriberCount): float {
        if ($subscriberCount <= 0 || $price <= 0) return 0.0;

        return match ($interval) {
            'year' => round(($price / 12.0) * $subscriberCount, 2),
            'lifetime' => 0.0,
            default => round($price * $subscriberCount, 2)
        };
    }
}
