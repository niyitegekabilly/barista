<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Refund extends Model {
    protected static string $table = 'refunds';

    public static function generateRefundNumber(): string {
        $prefix = 'BBA-REF-' . date('Ym');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $num = $prefix . '-' . $random;

        while (Database::fetchOne("SELECT id FROM refunds WHERE refund_number = :num", ['num' => $num])) {
            $random = strtoupper(bin2hex(random_bytes(2)));
            $num = $prefix . '-' . $random;
        }

        return $num;
    }
}
