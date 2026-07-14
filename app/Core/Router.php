<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, array $handler): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[strtoupper($method)][$path] = $handler;
    }

    public function dispatch(Request $request): array
    {
        $method = $request->method();
        $path = $request->path();

        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }

        throw new \RuntimeException('Rota não encontrada.', 404);
    }
}
