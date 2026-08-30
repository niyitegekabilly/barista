<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Order extends Model {
    protected static string $table = 'orders';

    public static function findWithRelations(int $orderId): ?array {
        $order = self::find($orderId);
        if (!$order) {
            return null;
        }

        $order['user'] = Database::fetchOne("SELECT id, name, email, role_id, created_at FROM users WHERE id = :uid", ['uid' => $order['user_id']]);
        if ($order['user']) {
            $order['user']['profile'] = Database::fetchOne("SELECT * FROM user_profiles WHERE user_id = :uid", ['uid' => $order['user_id']]);
        }

        $order['items'] = Database::fetchAll(
            "SELECT oi.*, c.slug as course_slug, c.thumbnail as course_thumbnail
             FROM order_items oi
             LEFT JOIN courses c ON (oi.item_type = 'course' AND oi.item_id = c.id)
             WHERE oi.order_id = :oid",
            ['oid' => $orderId]
        );

        $order['payments'] = Database::fetchAll("SELECT * FROM payments WHERE order_id = :oid ORDER BY id DESC", ['oid' => $orderId]);
        $order['latest_payment'] = $order['payments'][0] ?? null;

        $order['invoices'] = Database::fetchAll("SELECT * FROM invoices WHERE order_id = :oid ORDER BY id DESC", ['oid' => $orderId]);
        $order['receipts'] = Database::fetchAll("SELECT * FROM receipts WHERE order_id = :oid ORDER BY id DESC", ['oid' => $orderId]);
        $order['refunds'] = Database::fetchAll(
            "SELECT r.*, u.name as requested_by_name, a.name as approved_by_name
             FROM refunds r
             LEFT JOIN users u ON r.requested_by = u.id
             LEFT JOIN users a ON r.approved_by = a.id
             WHERE r.order_id = :oid ORDER BY r.id DESC",
            ['oid' => $orderId]
        );

        $order['notes'] = Database::fetchAll(
            "SELECT onotes.*, u.name as author_name
             FROM order_notes onotes
             LEFT JOIN users u ON onotes.user_id = u.id
             WHERE onotes.order_id = :oid ORDER BY onotes.created_at DESC",
            ['oid' => $orderId]
        );

        // Fetch related enrollment(s)
        $firstItem = $order['items'][0] ?? null;
        if ($firstItem && $firstItem['item_type'] === 'course') {
            $order['enrollment'] = Database::fetchOne(
                "SELECT e.*, l.title as last_lesson_title
                 FROM enrollments e
                 LEFT JOIN lessons l ON e.last_accessed_lesson_id = l.id
                 WHERE e.user_id = :uid AND e.course_id = :cid LIMIT 1",
                ['uid' => $order['user_id'], 'cid' => $firstItem['item_id']]
            );
        } else {
            $order['enrollment'] = null;
        }

        $order['remaining_refundable'] = static::getRemainingRefundableAmount($orderId);

        return $order;
    }

    public static function findByOrderNumber(string $orderNumber): ?array {
        $order = Database::fetchOne("SELECT * FROM orders WHERE order_number = :num LIMIT 1", ['num' => trim($orderNumber)]);
        if ($order) {
            return static::findWithRelations((int)$order['id']);
        }
        return null;
    }

    public static function getRemainingRefundableAmount(int $orderId): float {
        $order = Database::fetchOne("SELECT final_amount FROM orders WHERE id = :id", ['id' => $orderId]);
        if (!$order) return 0.00;

        $totalPaid = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE order_id = :oid AND status IN ('successful', 'partially_refunded', 'refunded')",
            ['oid' => $orderId]
        );

        $totalRefunded = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM refunds WHERE order_id = :oid AND status IN ('processed', 'approved')",
            ['oid' => $orderId]
        );

        return max(0.00, $totalPaid - $totalRefunded);
    }

    public static function generateOrderNumber(): string {
        $prefix = 'BBA-ORD-' . date('Ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        $orderNumber = $prefix . '-' . $random;

        while (Database::fetchOne("SELECT id FROM orders WHERE order_number = :num", ['num' => $orderNumber])) {
            $random = strtoupper(bin2hex(random_bytes(3)));
            $orderNumber = $prefix . '-' . $random;
        }

        return $orderNumber;
    }
}
