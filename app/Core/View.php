<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->basePath . '/resources/views/' . $template . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("View não encontrada: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
