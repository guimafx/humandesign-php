<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\ChannelCalculator;
use App\Services\TypeCalculator;

$reference = require __DIR__ . '/reference/guilherme.php';

if ($reference['expected']['type'] !== 'generator') {
    throw new RuntimeException('Tipo do mapa Guilherme divergente.');
}

$calculator = new TypeCalculator(new ChannelCalculator());
$cases = [
    'Reflector' => [[], [], 'reflector'],
    'Generator' => [['Sacral', 'Root'], ['3-60'], 'generator'],
    'Manifesting Generator direto' => [['Sacral', 'Throat'], ['20-34'], 'manifesting_generator'],
    'Manifesting Generator por caminho' => [['Sacral', 'G', 'Throat'], ['2-14', '1-8'], 'manifesting_generator'],
    'Manifestor' => [['Ego', 'Throat'], ['21-45'], 'manifestor'],
    'Projector' => [['Ajna', 'Throat'], ['17-62'], 'projector'],
];

foreach ($cases as $label => [$centers, $channels, $expectedId]) {
    $actual = $calculator->calculate($centers, $channels);
    if ($actual['id'] !== $expectedId) {
        throw new RuntimeException("{$label}: tipo esperado {$expectedId}, obtido {$actual['id']}.");
    }
}

echo "Type test OK\n";
