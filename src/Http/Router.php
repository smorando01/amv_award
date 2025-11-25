<?php
declare(strict_types=1);

namespace App\Http;

use Closure;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $normalizedPath = $this->normalizePath($path);
        $this->routes[strtoupper($method)][$normalizedPath] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?? '/');

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler instanceof Closure && !is_callable($handler)) {
            Response::json(['error' => 'Ruta no encontrada'], 404);
            return;
        }

        $handler();
    }

    private function normalizePath(string $path): string
    {
        $clean = '/' . ltrim($path, '/');
        if ($clean !== '/' && str_ends_with($clean, '/')) {
            $clean = rtrim($clean, '/');
        }
        return $clean;
    }
}

