<?php

namespace App\Core;

class Router {
    private static array $routes = [];
    private static array $groupMiddlewares = [];
    private static string $groupPrefix = '';

    public static function get(string $uri, array|callable|string $action, array $middlewares = []): void {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    public static function post(string $uri, array|callable|string $action, array $middlewares = []): void {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    public static function put(string $uri, array|callable|string $action, array $middlewares = []): void {
        self::addRoute('PUT', $uri, $action, $middlewares);
    }

    public static function delete(string $uri, array|callable|string $action, array $middlewares = []): void {
        self::addRoute('DELETE', $uri, $action, $middlewares);
    }

    public static function match(array $methods, string $uri, array|callable|string $action, array $middlewares = []): void {
        foreach ($methods as $method) {
            self::addRoute(strtoupper($method), $uri, $action, $middlewares);
        }
    }

    public static function any(string $uri, array|callable|string $action, array $middlewares = []): void {
        self::match(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $action, $middlewares);
    }

    public static function group(array $attributes, callable $callback): void {
        $previousPrefix = self::$groupPrefix;
        $previousMiddlewares = self::$groupMiddlewares;

        if (isset($attributes['prefix'])) {
            self::$groupPrefix = $previousPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $m = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            self::$groupMiddlewares = array_merge(self::$groupMiddlewares, $m);
        }

        $callback();

        self::$groupPrefix = $previousPrefix;
        self::$groupMiddlewares = $previousMiddlewares;
    }

    private static function addRoute(string $method, string $uri, array|callable|string $action, array $middlewares): void {
        $fullUri = self::$groupPrefix . '/' . trim($uri, '/');
        $fullUri = '/' . trim($fullUri, '/');

        $allMiddlewares = array_merge(self::$groupMiddlewares, $middlewares);

        // Convert {param} to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $fullUri);
        $pattern = '#^' . $pattern . '$#';

        self::$routes[] = [
            'method' => $method,
            'uri' => $fullUri,
            'pattern' => $pattern,
            'action' => $action,
            'middlewares' => $allMiddlewares
        ];
    }

    public static function dispatch(Request $request): void {
        $method = $request->method();
        $uri = $request->uri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named route parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                // Build middleware execution pipeline
                $pipeline = array_reverse($route['middlewares']);
                $handler = function (Request $req) use ($route, $params) {
                    return self::executeAction($route['action'], $req, $params);
                };

                foreach ($pipeline as $middlewareClass) {
                    $next = $handler;
                    $middlewareInstance = self::resolveMiddleware($middlewareClass);
                    $handler = function (Request $req) use ($middlewareInstance, $next) {
                        return $middlewareInstance->handle($req, $next);
                    };
                }

                $handler($request);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        if ($request->isAjax()) {
            Response::json(['success' => false, 'message' => 'Endpoint not found.'], 404);
        } else {
            echo View::render('errors/404', ['title' => 'Page Not Found'], 'layouts/main');
        }
    }

    private static function resolveMiddleware(mixed $middleware): Middleware {
        if (is_string($middleware)) {
            if (str_contains($middleware, ':')) {
                [$name, $param] = explode(':', $middleware, 2);
                if ($name === 'role') {
                    return new \App\Middleware\RoleMiddleware(explode(',', $param));
                }
            }
            if ($middleware === 'auth') {
                return new \App\Middleware\AuthMiddleware();
            }
            if ($middleware === 'csrf') {
                return new \App\Middleware\CsrfMiddleware();
            }
            if (class_exists($middleware)) {
                return new $middleware();
            }
        } elseif ($middleware instanceof Middleware) {
            return $middleware;
        }

        throw new \RuntimeException("Unable to resolve middleware: " . print_r($middleware, true));
    }

    private static function executeAction(array|callable|string $action, Request $request, array $params): mixed {
        if (is_callable($action)) {
            return call_user_func_array($action, array_merge([$request], array_values($params)));
        }

        if (is_array($action)) {
            [$controllerClass, $method] = $action;
            $controller = new $controllerClass();
            return call_user_func_array([$controller, $method], array_merge([$request], array_values($params)));
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controllerClass, $method] = explode('@', $action);
            $fullClass = "App\\Controllers\\{$controllerClass}";
            $controller = new $fullClass();
            return call_user_func_array([$controller, $method], array_merge([$request], array_values($params)));
        }

        throw new \RuntimeException("Invalid route action specified.");
    }
}
