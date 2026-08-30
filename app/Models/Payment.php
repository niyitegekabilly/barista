<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Payment extends Model {
    protected static string $table = 'payments';

    public static function findByTransactionReference(string $reference): ?array {
        return Database::fetchOne(
            "SELECT p.*, o.order_number, o.user_id as order_user_id, u.name as customer_name, u.email as customer_email
             FROM payments p
             JOIN orders o ON p.order_id = o.id
             JOIN users u ON p.user_id = u.id
             WHERE p.transaction_reference = :ref LIMIT 1",
            ['ref' => trim($reference)]
        );
    }

    public static function generateTransactionReference(string $gateway = 'sandbox'): string {
        $prefix = 'TXN-' . strtoupper(substr($gateway, 0, 4)) . '-' . date('YmdHis');
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(2)));
    }
}
