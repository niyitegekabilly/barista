<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class FinancialTransaction extends Model {
    protected static string $table = 'financial_transactions';

    public static function generateTransactionNumber(): string {
        $prefix = 'FTX-' . date('Ymd');
        $random = strtoupper(bin2hex(random_bytes(3)));
        $num = $prefix . '-' . $random;

        while (Database::fetchOne("SELECT id FROM financial_transactions WHERE transaction_number = :num", ['num' => $num])) {
            $random = strtoupper(bin2hex(random_bytes(3)));
            $num = $prefix . '-' . $random;
        }

        return $num;
    }

    public static function record(array $data): int {
        if (empty($data['transaction_number'])) {
            $data['transaction_number'] = static::generateTransactionNumber();
        }
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return Database::insert('financial_transactions', $data);
    }
}
