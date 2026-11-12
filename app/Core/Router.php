<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
            'regex'   => $this->buildRegex($path),
            'params'  => $this->extractParamNames($path),
        ];
    }

    private function buildRegex(string $path): string
    {
        // Parameters named {id} or ending with _id are constrained to digits only
        $pattern = preg_replace('/\{(id|[a-z_]*id)\}/', '(\d+)', $path);
        $pattern = preg_replace('/\{([a-z_]+)\}/', '([^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    private function extractParamNames(string $path): array
    {
        preg_match_all('/\{([a-z_]+)\}/', $path, $matches);
        return $matches[1];
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            // Build named params
            array_shift($matches);
            $params = array_combine($route['params'], $matches) ?: [];

            $this->callHandler($route['handler'], $params);
            return;
        }

        // 404
        http_response_code(404);
        $controller = new \App\Controllers\ErrorController();
        $controller->notFound();
    }

    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $action] = explode('@', $handler);

        // Resolve fully qualified class name
        $class = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller {$class} not found");
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action {$action} not found in {$class}");
        }

        $controller->$action($params);
    }
}

