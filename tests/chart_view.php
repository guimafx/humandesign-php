<?php

declare(strict_types=1);

$view = file_get_contents(dirname(__DIR__) . '/resources/views/chart.php');

if ($view === false) {
    throw new RuntimeException('Não foi possível ler a view do resultado.');
}

foreach (['id="copy-json-button"', 'id="chart-json"', 'Copiar JSON'] as $expected) {
    if (!str_contains($view, $expected)) {
        throw new RuntimeException("Conteúdo esperado ausente na view: {$expected}");
    }
}

echo "Chart view test OK\n";
