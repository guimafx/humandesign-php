<?php

declare(strict_types=1);

use App\Domain\BirthData;
use App\Services\DemoEphemerisProvider;
use App\Services\HumanDesignCalculator;

require dirname(__DIR__) . '/app/Core/Autoloader.php';

fwrite(STDERR, "AVISO: modo demo; os valores não são astronômicos e não formam um mapa real.\n");

$birth = BirthData::fromArray([
    'name' => 'Exemplo demonstrativo',
    'date' => '1987-05-14',
    'time' => '13:30',
    'timezone' => 'America/Sao_Paulo',
    'latitude' => '',
    'longitude' => '',
]);

$chart = (new HumanDesignCalculator(new DemoEphemerisProvider()))->calculate($birth);

echo json_encode(
    $chart,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
) . PHP_EOL;
