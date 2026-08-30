<?php

/**
 * Beyond Barista Academy — Front Controller
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Set up autoloader (Check vendor/autoload.php first, fallback to native PSR-4 autoloader)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = BASE_PATH . '/app/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });

    require BASE_PATH . '/app/Helpers/helpers.php';
}

// Error handling & Debugging
$debug = config('app.debug', false);
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Set application timezone
date_default_timezone_set(config('app.timezone', 'Africa/Kigali'));

// Start secure session
\App\Core\Session::start();

// Handle language switcher parameter ?lang=en / ?lang=rw / ?lang=fr
if (isset($_GET['lang'])) {
    $lang = strtolower((string)$_GET['lang']);
    if (in_array($lang, ['en', 'fr', 'rw'], true)) {
        \App\Core\Session::set('locale', $lang);
    }
}

// Load misc controllers (multi-class file)
require BASE_PATH . '/app/Controllers/MiscControllers.php';

// Bootstrap router alias so routes/web.php can use $router->get(), post(), put(), delete(), match(), etc.
$router = new class {
    public function get(string $uri, array|callable|string $action, array $mw = []): void {
        \App\Core\Router::get($uri, $action, $mw);
    }
    public function post(string $uri, array|callable|string $action, array $mw = []): void {
        \App\Core\Router::post($uri, $action, $mw);
    }
    public function put(string $uri, array|callable|string $action, array $mw = []): void {
        \App\Core\Router::put($uri, $action, $mw);
    }
    public function delete(string $uri, array|callable|string $action, array $mw = []): void {
        \App\Core\Router::delete($uri, $action, $mw);
    }
    public function match(array $methods, string $uri, array|callable|string $action, array $mw = []): void {
        \App\Core\Router::match($methods, $uri, $action, $mw);
    }
    public function any(string $uri, array|callable|string $action, array $mw = []): void {
        \App\Core\Router::any($uri, $action, $mw);
    }
    public function group(array $attributes, callable $callback): void {
        \App\Core\Router::group($attributes, $callback);
    }
    public function __call(string $name, array $args): mixed {
        return forward_static_call_array([\App\Core\Router::class, $name], $args);
    }
};

require BASE_PATH . '/routes/web.php';

// Dispatch the request
$request = new \App\Core\Request();
\App\Core\Router::dispatch($request);
