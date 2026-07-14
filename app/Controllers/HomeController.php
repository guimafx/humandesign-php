<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends BaseController
{
    public function index(): string
    {
        return $this->view->render('home', [
            'title' => 'Calculadora Human Design',
        ]);
    }

    public function health(): array
    {
        return [
            'success' => true,
            'application' => 'humandesign-php',
            'php' => PHP_VERSION,
            'time' => date(DATE_ATOM),
        ];
    }
}
