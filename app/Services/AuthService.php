<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use App\Core\Session;
use App\Core\Database;

class AuthService {

    /**
     * Authenticate user with email and password.
     */
    public function login(string $email, string $password, bool $remember = false): array {
        return static::attemptLogin($email, $password, $remember);
    }

    public static function attemptLogin(string $email, string $password, bool $remember = false): array {
        $email = strtolower(trim($email));
        $user = User::findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'No account found with that email address. Please check and try again, or <a href="/register">create a new account</a>.'];
        }

        // Check if locked
        if ($user['status'] === 'locked' || (!empty($user['locked_until']) && strtotime($user['locked_until']) > time())) {
            \App\Models\UserLogin::log((int)$user['id'], 'locked');
            return ['success' => false, 'message' => 'Your account is temporarily locked due to security policy. Please contact an administrator at info@beyondbarista.rw.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is currently ' . $user['status'] . '. Please contact support at info@beyondbarista.rw for assistance.'];
        }

        if (!password_verify($password, $user['password'])) {
            // Track failed attempts
            $fails = ((int)($user['failed_login_attempts'] ?? 0)) + 1;
            $lockedUntil = $fails >= 5 ? date('Y-m-d H:i:s', strtotime('+30 minutes')) : null;
            $status = $fails >= 5 ? 'locked' : $user['status'];
            
            Database::update('users', [
                'failed_login_attempts' => $fails,
                'locked_until' => $lockedUntil,
                'status' => $status
            ], ['id' => $user['id']]);

            \App\Models\UserLogin::log((int)$user['id'], 'failed');

            return ['success' => false, 'message' => 'Incorrect password. Please try again or <a href="/forgot-password">reset your password</a>.'];
        }

        // Fetch full profile and store in session
        $fullUser = User::findWithProfile((int)$user['id']);
        unset($fullUser['password']);

        Session::regenerate();
        Session::set('user', $fullUser);

        \App\Models\UserLogin::log((int)$user['id'], 'success');
        AuditLog::log('user_login', 'user', (int)$user['id'], ['email' => $email]);

        return [
            'success' => true,
            'user' => $fullUser
        ];
    }

    public static function attempt(string $email, string $password, bool $remember = false): bool {
        $res = static::attemptLogin($email, $password, $remember);
        return $res['success'];
    }

    /**
     * Register new user account.
     */
    public function register(array $data, string $roleSlug = 'student'): array {
        return static::registerUser($data, $roleSlug);
    }

    public static function registerUser(array $data, string $roleSlug = 'student'): array {
        $email = strtolower(trim($data['email']));

        $existing = User::findByEmail($email);
        if ($existing) {
            return ['success' => false, 'message' => 'An account with this email address already exists.'];
        }

        $role = Database::fetchOne("SELECT id FROM roles WHERE slug = :slug LIMIT 1", ['slug' => $roleSlug]);
        $roleId = $role ? (int)$role['id'] : 2;

        $userId = User::create([
            'role_id' => $roleId,
            'name' => trim($data['name']),
            'email' => $email,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'status' => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        Database::insert('user_profiles', [
            'user_id' => $userId,
            'phone' => $data['phone'] ?? null,
            'country' => 'Rwanda',
            'city' => $data['city'] ?? 'Kigali',
            'language' => Session::get('locale', 'en')
        ]);

        AuditLog::log('user_registered', 'user', $userId, ['email' => $email]);

        $fullUser = User::findWithProfile($userId);
        unset($fullUser['password']);

        return [
            'success' => true,
            'user' => $fullUser
        ];
    }

    /**
     * Send password reset link.
     */
    public function sendPasswordResetLink(string $email): array {
        $email = strtolower(trim($email));
        $user = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            Database::insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'expires_at' => $expires,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            AuditLog::log('password_reset_request', 'user', (int)$user['id'], ['email' => $email]);
        }

        return [
            'success' => true,
            'message' => 'If that email is registered in our system, a password reset link has been sent.'
        ];
    }

    /**
     * Logout authenticated user.
     */
    public function logout(): void {
        $userId = auth_id();
        if ($userId) {
            AuditLog::log('user_logout', 'user', $userId);
        }
        Session::destroy();
    }
}
