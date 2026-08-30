<?php

namespace App\Core;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Render a view inside a layout and output it.
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $layout = ltrim(str_replace('layouts/', '', $layout), '/');
        if ($layout === 'admin') {
            $layout = 'dashboard';
        }
        $layoutFile = 'layouts/' . ($layout ?: 'main');
        echo View::render($view, $data, $layoutFile);
    }

    /**
     * Return the PDO Database wrapper singleton.
     */
    protected function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Return a JSON response.
     */
    protected function json(array|object $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        if (!str_starts_with($url, 'http')) {
            $url = url($url);
        }
        Response::redirect($url, $statusCode);
    }

    protected function back(): void
    {
        Response::back();
    }

    /**
     * Store a flash message.
     */
    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    /**
     * Abort with HTTP error code.
     */
    protected function abort(int $code): void
    {
        http_response_code($code);
        $view = BASE_PATH . "/resources/views/errors/$code.php";
        if (file_exists($view)) {
            include $view;
        } else {
            echo "<h1>Error $code</h1>";
        }
        exit;
    }

    /**
     * Validate an array of data against rules.
     * Returns an array of error messages (empty if valid).
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return [];
    }
}
