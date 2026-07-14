<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;

abstract class BaseController
{
    public function __construct(
        protected readonly Request $request,
        protected readonly View $view,
        protected readonly array $services
    ) {
    }

    protected function service(string $class): object
    {
        if (!isset($this->services[$class])) {
            throw new \RuntimeException("Serviço não registrado: {$class}");
        }

        return $this->services[$class];
    }
}
