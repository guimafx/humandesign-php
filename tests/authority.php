<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\AuthorityCalculator;

$reference = require __DIR__ . '/reference/guilherme.php';

if ($reference['expected']['authority'] !== 'emotional') {
    throw new RuntimeException('Autoridade do mapa Guilherme divergente.');
}

$calculator = new AuthorityCalculator();
$cases = [
    'emotional' => ['Solar Plexus', 'Sacral', 'Spleen', 'Ego', 'G'],
    'sacral' => ['Sacral', 'Spleen', 'Ego', 'G'],
    'splenic' => ['Spleen', 'Ego', 'G'],
    'ego' => ['Ego', 'G'],
    'self_projected' => ['G', 'Throat'],
    'mental' => ['Head', 'Ajna'],
    'lunar' => [],
];

foreach ($cases as $expectedId => $centers) {
    $actual = $calculator->calculate($centers);
    if ($actual['id'] !== $expectedId) {
        throw new RuntimeException("Autoridade esperada {$expectedId}, obtida {$actual['id']}.");
    }
}

echo "Authority test OK\n";
