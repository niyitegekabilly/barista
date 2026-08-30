<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Setting extends Model {
    protected static string $table = 'settings';

    public static function get(string $key, mixed $default = null): mixed {
        $val = Database::fetchValue("SELECT value FROM settings WHERE `key` = :k LIMIT 1", ['k' => $key]);
        return $val !== false ? $val : $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void {
        $existing = self::findBy('key', $key);
        if ($existing) {
            Database::update('settings', ['value' => (string)$value, 'updated_at' => date('Y-m-d H:i:s')], "`key` = :k", ['k' => $key]);
        } else {
            Database::insert('settings', [
                'key' => $key,
                'value' => (string)$value,
                'group' => $group,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
