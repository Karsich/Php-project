<?php

namespace App\Core\Controllers;

class ViewController
{
    private $layout = 'main';
    private $viewsPath;

    public function __construct()
    {
        $this->viewsPath = dirname(__DIR__, 2) . '/Views/';
    }

    public function render($view, $params = [])
    {
        $content = $this->renderView($view, $params);
        return $this->renderLayout($content, $params);
    }

    private function renderView($view, $params)
    {
        $viewFile = $this->viewsPath . $view . '.php';
        return $this->renderPhpFile($viewFile, $params);
    }

    private function renderLayout($content, $params)
    {
        $params['content'] = $content;
        $layoutFile = $this->viewsPath . 'layouts/' . $this->layout . '.php';
        return $this->renderPhpFile($layoutFile, $params);
    }

    private function renderPhpFile($file, $params)
    {
        extract($params);
        ob_start();
        include $file;
        return ob_get_clean();
    }
} 