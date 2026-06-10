<?php

class Router
{
    private array $routes = [];

    public function get(string $pattern, string $controller, string $method): void
    {
        $this->routes[] = ['GET', $pattern, $controller, $method];
    }

    public function post(string $pattern, string $controller, string $method): void
    {
        $this->routes[] = ['POST', $pattern, $controller, $method];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $pattern, $controller, $action]) {
            if ($routeMethod !== $method) continue;

            $regex  = '@^' . preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern) . '$@';
            if (!preg_match($regex, $uri, $matches)) continue;

            array_shift($matches);
            $ctrl = new $controller();
            $ctrl->$action(...$matches);
            return;
        }

        http_response_code(404);
        $ctrl = new Controller();
        // анонимный класс не подходит — используем прямой вывод
        require BASE_PATH . '/app/views/errors/404.php';
    }
}
