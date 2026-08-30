<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Permission extends Model {
    protected static string $table = 'permissions';

    public static function allGroupedByModule(): array {
        $permissions = Database::fetchAll("SELECT * FROM permissions ORDER BY module ASC, name ASC");
        $grouped = [];
        foreach ($permissions as $p) {
            $module = $p['module'] ?: 'general';
            $grouped[$module][] = $p;
        }
        return $grouped;
    }
}
