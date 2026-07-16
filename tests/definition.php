<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\CenterCalculator;
use App\Services\ChannelCalculator;
use App\Services\DefinitionCalculator;

$loadReference = require __DIR__ . '/reference/guilherme.php';
$reference = $loadReference();

if ($reference['chart']['definition'] !== $reference['expected']['definition']) {
    throw new RuntimeException('Definição do mapa Guilherme divergente.');
}

$channelCalculator = new ChannelCalculator();
$centerCalculator = new CenterCalculator($channelCalculator);
$calculator = new DefinitionCalculator($channelCalculator);
$cases = [
    'single' => ['17-62'],
    'split' => ['4-63', '1-8'],
    'triple_split' => ['4-63', '1-8', '37-40'],
    'quadruple_split' => ['4-63', '1-8', '37-40', '18-58'],
];

foreach ($cases as $expectedId => $channels) {
    $centers = $centerCalculator->calculate($channels);
    $actual = $calculator->calculate($centers, $channels);
    if ($actual['id'] !== $expectedId) {
        throw new RuntimeException("Definição esperada {$expectedId}, obtida {$actual['id']}.");
    }
}

echo "Definition test OK\n";
