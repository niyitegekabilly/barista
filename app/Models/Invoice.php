<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Invoice extends Model {
    protected static string $table = 'invoices';

    public static function findByInvoiceNumber(string $number): ?array {
        $inv = Database::fetchOne("SELECT * FROM invoices WHERE invoice_number = :num LIMIT 1", ['num' => trim($number)]);
        if ($inv) {
            $inv['order'] = Order::findWithRelations((int)$inv['order_id']);
            $inv['user'] = Database::fetchOne("SELECT id, name, email FROM users WHERE id = :uid", ['uid' => $inv['user_id']]);
        }
        return $inv;
    }

    public static function generateInvoiceNumber(): string {
        $prefix = 'BBA-INV-' . date('Ym');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $num = $prefix . '-' . $random;

        while (Database::fetchOne("SELECT id FROM invoices WHERE invoice_number = :num", ['num' => $num])) {
            $random = strtoupper(bin2hex(random_bytes(2)));
            $num = $prefix . '-' . $random;
        }

        return $num;
    }
}
