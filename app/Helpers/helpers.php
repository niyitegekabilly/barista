<?php

/**
 * Global helper functions for Beyond Barista Academy LMS
 */

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        static $envVars = null;
        if ($envVars === null) {
            $envVars = [];
            $envFile = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    if (str_contains($line, '=')) {
                        [$name, $value] = explode('=', $line, 2);
                        $name = trim($name);
                        $value = trim($value);
                        // Strip quotes if present
                        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                            $value = substr($value, 1, -1);
                        }
                        $envVars[$name] = $value;
                    }
                }
            }
        }
        return $envVars[$key] ?? getenv($key) ?? $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        static $configs = [];
        $parts = explode('.', $key);
        $file = $parts[0];
        
        if (!isset($configs[$file])) {
            $path = dirname(__DIR__, 2) . "/config/{$file}.php";
            if (file_exists($path)) {
                $configs[$file] = require $path;
            } else {
                $configs[$file] = [];
            }
        }
        
        $value = $configs[$file];
        array_shift($parts);
        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string {
        static $resolvedBaseUrl = null;
        if ($resolvedBaseUrl === null) {
            $configured = config('app.url', '');
            $isLiveHost = isset($_SERVER['HTTP_HOST']) && !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']);
            $hasPlaceholder = empty($configured) || str_contains($configured, 'yourdomain.com') || ($isLiveHost && str_contains($configured, 'localhost'));
            
            if (!$hasPlaceholder && !empty($configured)) {
                $resolvedBaseUrl = rtrim($configured, '/');
            } elseif (isset($_SERVER['HTTP_HOST'])) {
                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
                           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
                $protocol = $isHttps ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                
                $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                $baseDir = dirname($scriptName);
                $baseDir = preg_replace('#/public$#', '', $baseDir);
                $baseDir = ($baseDir === '/' || $baseDir === '\\' || $baseDir === '.') ? '' : rtrim($baseDir, '/');
                
                $resolvedBaseUrl = $protocol . $host . $baseDir;
            } else {
                $resolvedBaseUrl = rtrim($configured ?: 'http://localhost/bbacademy', '/');
            }
        }
        $path = ltrim($path, '/');
        return $path === '' ? $resolvedBaseUrl : "{$resolvedBaseUrl}/{$path}";
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return app_url($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $path = trim($path);
        if ($path === '') {
            return app_url('assets/img/barista.jpeg');
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'assets/')) {
            return app_url($path);
        }
        if (str_starts_with($path, 'uploads/')) {
            return app_url($path);
        }
        return app_url("assets/{$path}");
    }
}

if (!function_exists('storage_url')) {
    function storage_url(string $path): string {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'uploads/')) {
            return app_url($path);
        }
        return app_url("uploads/{$path}");
    }
}

if (!function_exists('nav_active')) {
    function nav_active(string $path = '', string $activeClass = 'active'): string {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $scriptDir = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = trim(substr($uri, strlen($scriptDir)), '/');
        }
        $target = trim($path, '/');
        if ($target === '' && ($uri === '' || $uri === 'home')) {
            return $activeClass;
        }
        if ($target !== '' && (str_starts_with($uri, $target) || $uri === $target)) {
            return $activeClass;
        }
        return '';
    }
}

if (!function_exists('course_thumbnail')) {
    function course_thumbnail(?string $thumb = null, int $index = 0): string {
        $fallbacks = [
            'assets/img/barista.jpeg',
            'assets/img/cappuccino.jpg',
            'assets/img/coffee-cups.jpg',
            'assets/img/coffeshop.jpg',
            'assets/img/class.png',
            'assets/img/best.jpg',
            'assets/img/teachers.jpg',
            'assets/img/herosection.jpg',
        ];

        if (!empty($thumb)) {
            $thumb = trim($thumb);
            if (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://')) {
                return $thumb;
            }
            if (str_starts_with($thumb, 'uploads/')) {
                return app_url($thumb);
            }
            if (str_starts_with($thumb, 'assets/')) {
                return app_url($thumb);
            }
            if (str_starts_with($thumb, 'img/')) {
                return app_url('assets/' . $thumb);
            }
            return app_url('assets/uploads/' . $thumb);
        }

        $chosen = $fallbacks[$index % count($fallbacks)];
        return app_url($chosen);
    }
}

if (!function_exists('lesson_thumbnail')) {
    function lesson_thumbnail(mixed $lesson = null, int $index = 0): string {
        $fallbacks = [
            'assets/img/barista.jpeg',
            'assets/img/cappuccino.jpg',
            'assets/img/coffee-cups.jpg',
            'assets/img/coffeshop.jpg',
            'assets/img/class.png',
            'assets/img/best.jpg',
        ];

        if (is_array($lesson)) {
            $url = $lesson['video_url'] ?? '';
            $provider = strtolower($lesson['video_provider'] ?? '');

            if (!empty($url)) {
                // If YouTube, get YouTube HQ thumbnail
                if ($provider === 'youtube' || str_contains($url, 'youtu')) {
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $m)) {
                        return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
                    }
                }
                // If Vimeo, use vumbnail
                if ($provider === 'vimeo' || str_contains($url, 'vimeo.com')) {
                    if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|video\/|)(\d+)/i', $url, $m)) {
                        $vId = $m[2] ?? $m[1];
                        return "https://vumbnail.com/{$vId}.jpg";
                    }
                }
            }

            if (!empty($lesson['thumbnail'])) {
                $thumb = trim($lesson['thumbnail']);
                // Absolute URL
                if (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://')) {
                    return $thumb;
                }
                // Uploaded file (uploads directory or exists in public folder)
                if (str_starts_with($thumb, 'uploads/') || file_exists(dirname(__DIR__, 2) . '/public/' . ltrim($thumb, '/'))) {
                    return storage_url($thumb);
                }
                // Asset path
                if (str_starts_with($thumb, 'assets/') || str_starts_with($thumb, 'img/')) {
                    return app_url($thumb);
                }
                // Fallback to assets/uploads
                return app_url('assets/uploads/' . $thumb);
            }
        }

        $chosen = $fallbacks[$index % count($fallbacks)];
        return app_url($chosen);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('sanitize')) {
    function sanitize(mixed $data): mixed {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return is_string($data) ? trim(strip_tags($data)) : $data;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \App\Core\Csrf::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return \App\Core\Csrf::field();
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, mixed $default = null): mixed {
        if ($key === null) {
            return \App\Core\Session::class;
        }
        return \App\Core\Session::get($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array {
        return \App\Core\Session::get('user');
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool {
        return \App\Core\Session::has('user');
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int {
        $user = auth();
        return $user ? (int) $user['id'] : null;
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): string {
        $user = auth();
        return $user['role_slug'] ?? $user['role_name'] ?? 'visitor';
    }
}

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool {
        $currentRole = auth_role();
        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }
        return $currentRole === $roles;
    }
}

if (!function_exists('format_rwf')) {
    function format_rwf(float|int $amount): string {
        return 'RWF ' . number_format($amount, 0, '.', ',');
    }
}

if (!function_exists('format_price')) {
    function format_price(float|int $amount, string $currency = 'RWF'): string {
        if ($amount <= 0) {
            return __('app.free', 'Free');
        }
        return $currency . ' ' . number_format($amount, 0, '.', ',');
    }
}

if (!function_exists('__')) {
    function __(string $key, string $default = '', array $replace = []): string {
        static $translations = [];
        $locale = session('locale', config('app.locale', 'en'));
        
        $parts = explode('.', $key);
        $file = $parts[0] ?? 'app';
        $item = $parts[1] ?? $key;
        
        $cacheKey = "{$locale}.{$file}";
        if (!isset($translations[$cacheKey])) {
            $langPath = dirname(__DIR__, 2) . "/resources/lang/{$locale}/{$file}.php";
            if (file_exists($langPath)) {
                $translations[$cacheKey] = require $langPath;
            } else {
                $fallbackPath = dirname(__DIR__, 2) . "/resources/lang/en/{$file}.php";
                $translations[$cacheKey] = file_exists($fallbackPath) ? require $fallbackPath : [];
            }
        }
        
        $text = $translations[$cacheKey][$item] ?? ($default !== '' ? $default : $item);
        foreach ($replace as $placeholder => $val) {
            $text = str_replace(":{$placeholder}", (string) $val, $text);
        }
        return $text;
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'n-a');
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void {
        \App\Core\Session::flash($type, $message);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed {
        $old = \App\Core\Session::getFlash('old_input', []);
        return $old[$key] ?? $default;
    }
}

if (!function_exists('errors')) {
    function errors(string $key = ''): mixed {
        $errors = \App\Core\Session::getFlash('errors', []);
        if ($key === '') {
            return $errors;
        }
        return $errors[$key] ?? null;
    }
}

if (!function_exists('format_money')) {
    function format_money(float|int $amount, string $currency = 'RWF'): string {
        $currency = strtoupper(trim($currency ?: 'RWF'));
        if ($currency === 'USD') {
            return '$' . number_format((float)$amount, 2);
        }
        if ($currency === 'EUR') {
            return '€' . number_format((float)$amount, 2);
        }
        if ($currency === 'GBP') {
            return '£' . number_format((float)$amount, 2);
        }
        // Default to RWF
        return number_format((float)$amount, 0) . ' RWF';
    }
}

if (!function_exists('format_rwf')) {
    function format_rwf(float|int $amount): string {
        return number_format((float)$amount, 0) . ' RWF';
    }
}

if (!function_exists('format_usd')) {
    function format_usd(float|int $amount): string {
        return '$' . number_format((float)$amount, 2);
    }
}
