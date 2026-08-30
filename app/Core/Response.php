<?php

namespace App\Core;

class Response {
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';

    public function setStatusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function header(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    public function send(): void {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }
        echo $this->content;
        exit;
    }

    public static function json(array|object $data, int $statusCode = 200): void {
        $response = new self();
        $response->setStatusCode($statusCode)
                 ->header('Content-Type', 'application/json; charset=utf-8')
                 ->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                 ->send();
    }

    public static function redirect(string $url, int $statusCode = 302): void {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = app_url($url);
        }
        $response = new self();
        $response->setStatusCode($statusCode)
                 ->header('Location', $url)
                 ->send();
    }

    public static function back(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? app_url('/');
        self::redirect($referer);
    }
}
