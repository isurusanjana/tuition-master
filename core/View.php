<?php
/**
 * View - renders a PHP view inside the main layout (or standalone).
 */
class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            echo "View not found: $view";
            return;
        }
        if ($layout === null) {
            require $viewFile;
            return;
        }
        // capture the view content, then inject into layout via $content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = APP_PATH . '/views/' . $layout . '.php';
        require $layoutFile;
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data);
        require APP_PATH . '/views/' . $view . '.php';
    }
}
