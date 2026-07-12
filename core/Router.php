<?php
/**
 * Router - lightweight route matcher supporting GET/POST + dynamic {param} segments.
 */
class Router
{
    private array $routes = [];
    private array $namedRoutes = [];

    public function get(string $path, string $handler, string $name = ''): void
    {
        $this->add('GET', $path, $handler, $name);
    }

    public function post(string $path, string $handler, string $name = ''): void
    {
        $this->add('POST', $path, $handler, $name);
    }

    private function add(string $method, string $path, string $handler, string $name): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
        if ($name) $this->namedRoutes[$name] = $path;
    }

    public function url(string $name, array $params = []): string
    {
        $path = $this->namedRoutes[$name] ?? '/';
        foreach ($params as $k => $v) {
            $path = str_replace('{' . $k . '}', (string) $v, $path);
        }
        return APP_URL . $path;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        // strip the /public prefix if the app isn't served from a vhost root
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($script !== '/' && str_starts_with($uri, $script)) {
            $uri = substr($uri, strlen($script));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->call($route['handler'], $matches);
                return;
            }
        }
        http_response_code(404);
        View::render('errors/404');
    }

    private function call(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $file = APP_PATH . '/controllers/' . $controllerName . '.php';
        if (!file_exists($file)) {
            http_response_code(500);
            echo "Controller not found: $controllerName";
            return;
        }
        require_once $file;
        $controller = new $controllerName();
        call_user_func_array([$controller, $method], $params);
    }
}
