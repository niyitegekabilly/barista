<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Receipt extends Model {
    protected static string $table = 'receipts';

    public static function findByReceiptNumber(string $number): ?array {
        $rec = Database::fetchOne("SELECT * FROM receipts WHERE receipt_number = :num LIMIT 1", ['num' => trim($number)]);
        if ($rec) {
            $rec['order'] = Order::findWithRelations((int)$rec['order_id']);
            $rec['payment'] = Database::fetchOne("SELECT * FROM payments WHERE id = :pid", ['pid' => $rec['payment_id']]);
            $rec['user'] = Database::fetchOne("SELECT id, name, email FROM users WHERE id = :uid", ['uid' => $rec['user_id']]);
        }
        return $rec;
    }

    public static function generateReceiptNumber(): string {
        $prefix = 'BBA-REC-' . date('Ym');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $num = $prefix . '-' . $random;

        while (Database::fetchOne("SELECT id FROM receipts WHERE receipt_number = :num", ['num' => $num])) {
            $random = strtoupper(bin2hex(random_bytes(2)));
            $num = $prefix . '-' . $random;
        }

        return $num;
    }
}
