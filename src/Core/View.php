<?php

namespace App\Core;

class View
{
    private string $layout = 'layouts/main';

    public function render(string $view, array $data = []): string
    {
        $viewContent = $this->renderView($view, $data);
        return $this->renderLayout($viewContent, $data);
    }

    private function renderView(string $view, array $data): string
    {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/{$view}.php";
        return ob_get_clean();
    }

    private function renderLayout(string $content, array $data): string
    {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/{$this->layout}.php";
        return ob_get_clean();
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public static function setFlash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }
} 