<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\ActivationMapper;
use App\Services\SwissEphemerisProvider;

$provider = new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
);
$mapper = new ActivationMapper();
$utc = new DateTimeImmutable('1981-12-30 08:47:00', new DateTimeZone('UTC'));
$expected = [
    'SUN' => [58, 6],
    'EARTH' => [52, 6],
    'MOON' => [49, 4],
    'NORTH_NODE' => [62, 2],
    'SOUTH_NODE' => [61, 2],
    'MERCURY' => [54, 6],
    'VENUS' => [19, 2],
    'MARS' => [18, 3],
    'JUPITER' => [28, 5],
    'SATURN' => [32, 1],
    'URANUS' => [34, 3],
    'NEPTUNE' => [11, 3],
    'PLUTO' => [50, 1],
];
$divergences = [];

foreach ($expected as $body => [$expectedGate, $expectedLine]) {
    $longitude = $provider->longitude($utc, $body);
    $activation = $mapper->map($body, $longitude);

    if ($activation->gate !== $expectedGate || $activation->line !== $expectedLine) {
        $divergences[] = sprintf(
            '%s: esperado %d.%d, obtido %d.%d (longitude %.10f°)',
            $body,
            $expectedGate,
            $expectedLine,
            $activation->gate,
            $activation->line,
            $longitude
        );
    }
}

if ($divergences !== []) {
    throw new RuntimeException(
        "Divergências na referência Design de 1981-12-30 08:47:00 UTC:\n"
        . implode("\n", $divergences)
    );
}

echo "Design reference test OK\n";
