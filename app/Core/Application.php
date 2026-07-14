<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    private Request $request;
    private View $view;

    public function __construct(
        private readonly Router $router,
        private readonly string $basePath,
        private readonly array $services = []
    ) {
        $this->request = new Request();
        $this->view = new View($basePath);
    }

    public function get(string $path, array $handler): void
    {
        $this->router->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->router->add('POST', $path, $handler);
    }

    public function run(): never
    {
        try {
            [$controllerClass, $method] = $this->router->dispatch($this->request);

            $controller = new $controllerClass(
                $this->request,
                $this->view,
                $this->services
            );

            $result = $controller->{$method}();

            if (is_array($result)) {
                Response::json($result);
            }

            Response::html((string) $result);
        } catch (\Throwable $exception) {
            $status = $exception->getCode();
            $status = is_int($status) && $status >= 400 && $status <= 599 ? $status : 500;

            if ($this->expectsJson()) {
                Response::json([
                    'success' => false,
                    'error' => $exception->getMessage(),
                ], $status);
            }

            $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');

            Response::html(
                "<h1>Erro</h1><p>{$message}</p><p><a href='/'>Voltar</a></p>",
                $status
            );
        }
    }

    private function expectsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }
}
