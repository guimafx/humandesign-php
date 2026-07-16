<?php

declare(strict_types=1);

$title = 'Resultado de teste';
$birth = (object) ['name' => 'Pessoa de teste'];
$chart = [
    'metadata' => [
        'reliable' => true,
        'warning' => null,
        'ephemeris' => 'test-provider',
    ],
    'birth' => [
        'local' => '2026-07-16T12:00:00-03:00',
        'utc' => '2026-07-16T15:00:00+00:00',
    ],
    'sample' => '<tag>ação</tag>',
];

ob_start();
require dirname(__DIR__) . '/resources/views/chart.php';
$html = ob_get_clean();

if (!is_string($html)) {
    throw new RuntimeException('Não foi possível renderizar a view do resultado.');
}

$expectedContents = [
    'id="copy-json-button"',
    'data-copy-target="chart-json"',
    'id="chart-json"',
    'Copiar JSON',
    'aria-live="polite"',
    'navigator.clipboard',
    'navigator.clipboard.writeText',
    "document.createElement('textarea')",
    "document.execCommand('copy')",
    '.textContent',
    'DOMContentLoaded',
];

foreach ($expectedContents as $expected) {
    if (!str_contains($html, $expected)) {
        throw new RuntimeException("Conteúdo esperado ausente na view renderizada: {$expected}");
    }
}

if (!str_contains($html, '&lt;tag&gt;ação&lt;/tag&gt;')) {
    throw new RuntimeException('O JSON formatado não foi escapado corretamente no HTML.');
}

echo "Chart View test OK\n";
