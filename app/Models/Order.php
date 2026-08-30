<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Order extends Model {
    protected static string $table = 'orders';

    public static function getWithItems(int $orderId): ?array {
        $order = self::find($orderId);
        if ($order) {
            $order['items'] = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :oid", ['oid' => $orderId]);
            $order['payment'] = Database::fetchOne("SELECT * FROM payments WHERE order_id = :oid ORDER BY id DESC LIMIT 1", ['oid' => $orderId]);
            $order['user'] = Database::fetchOne("SELECT id, name, email FROM users WHERE id = :uid", ['uid' => $order['user_id']]);
        }
        return $order;
    }
}
