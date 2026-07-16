<?php

declare(strict_types=1);

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;
use App\Services\SwissEphemerisProvider;

require dirname(__DIR__) . '/app/Core/Autoloader.php';

$birth = BirthData::fromArray([
    'name' => 'Exemplo básico',
    'date' => '1982-03-27',
    'time' => '11:05',
    'timezone' => 'America/Sao_Paulo',
    'latitude' => '',
    'longitude' => '',
]);

$provider = new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
);
$chart = (new HumanDesignCalculator($provider))->calculate($birth);

echo json_encode(
    $chart,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
) . PHP_EOL;
