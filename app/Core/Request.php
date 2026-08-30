<?php

namespace App\Core;

class Request {
    private array $get;
    private array $post;
    private array $server;
    private array $files;
    private ?array $json = null;

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;

        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            $this->json = json_decode($rawBody, true) ?: [];
        }
    }

    public function method(): string {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST') {
            if (isset($this->post['_method'])) {
                return strtoupper($this->post['_method']);
            }
        }
        return $method;
    }

    public function isPost(): bool {
        return $this->method() === 'POST';
    }

    public function isGet(): bool {
        return $this->method() === 'GET';
    }

    public function isAjax(): bool {
        return (!empty($this->server['HTTP_X_REQUESTED_WITH']) &&
                strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function uri(): string {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        // Strip query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        // Normalize base path if project runs in subdirectory like /bbacademy or /bbacademy/public
        $scriptName = str_replace('\\', '/', $this->server['SCRIPT_NAME'] ?? '');
        $baseDir = dirname($scriptName); // e.g. /bbacademy/public or /bbacademy
        $projectBase = preg_replace('#/public$#', '', $baseDir);

        if ($projectBase !== '' && $projectBase !== '/' && str_starts_with($uri, $projectBase)) {
            $uri = substr($uri, strlen($projectBase));
        }

        // Strip /public if present in path
        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
        }

        $path = '/' . trim($uri, '/');
        return $path;
    }

    public function input(string $key, mixed $default = null): mixed {
        if ($this->json !== null && array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }
        return $default;
    }

    public function all(): array {
        if ($this->json !== null) {
            return array_merge($this->get, $this->json);
        }
        return array_merge($this->get, $this->post);
    }

    public function file(string $key): ?array {
        if (isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE) {
            return $this->files[$key];
        }
        return null;
    }

    public function header(string $key, mixed $default = null): mixed {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$serverKey] ?? $this->server[$key] ?? $default;
    }

    public function ip(): string {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
