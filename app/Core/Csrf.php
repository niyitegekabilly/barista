<?php

namespace App\Core;

class Csrf {
    private const TOKEN_KEY = '_csrf_token';

    public static function getToken(): string {
        Session::start();
        $token = Session::get(self::TOKEN_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    public static function field(): string {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }

    public static function validate(?string $token): bool {
        if (!$token) {
            return false;
        }
        Session::start();
        $stored = Session::get(self::TOKEN_KEY);
        if (!$stored) {
            return false;
        }
        return hash_equals($stored, $token);
    }
}
