<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Domain\BirthData;
use App\Services\DemoEphemerisProvider;
use App\Services\HumanDesignCalculator;

$birth = BirthData::fromArray([
    'name' => 'Smoke Test',
    'date' => '1987-05-14',
    'time' => '13:30',
    'timezone' => 'America/Sao_Paulo',
    'latitude' => '-27.5949',
    'longitude' => '-48.5482',
]);

$calculator = new HumanDesignCalculator(new DemoEphemerisProvider());
$result = $calculator->calculate($birth);

if (!isset(
    $result['metadata'],
    $result['personality']['SUN'],
    $result['design']['SUN'],
    $result['design_date'],
    $result['type'],
    $result['authority'],
    $result['definition']
) || $result['metadata']['reliable'] !== false) {
    throw new RuntimeException('Estrutura do resultado demonstrativo inválida.');
}

echo "Smoke test OK\n";
