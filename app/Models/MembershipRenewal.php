<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class MembershipRenewal extends Model {
    protected static string $table = 'membership_renewals';

    public static function record(array $data): int {
        return Database::insert('membership_renewals', $data);
    }
}
