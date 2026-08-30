<?php

namespace App\Core;

class View {
    private static array $sharedData = [];

    public static function share(string|array $key, mixed $value = null): void {
        if (is_array($key)) {
            self::$sharedData = array_merge(self::$sharedData, $key);
        } else {
            self::$sharedData[$key] = $value;
        }
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        // Merge shared data with view data
        $mergedData = array_merge(self::$sharedData, $data);
        extract($mergedData);

        // Capture view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // If layout specified, render inside layout
        if ($layout !== null) {
            $layoutPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout file not found: {$layoutPath}");
            }
            ob_start();
            require $layoutPath;
            return ob_get_clean();
        }

        return $content;
    }
}
