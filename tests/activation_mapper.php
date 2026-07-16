<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\ActivationMapper;
use App\Services\SwissEphemerisProvider;

function requireActivation(
    int $actualGate,
    int $actualLine,
    int $expectedGate,
    int $expectedLine,
    string $label
): void {
    if ($actualGate !== $expectedGate || $actualLine !== $expectedLine) {
        throw new RuntimeException(sprintf(
            '%s: esperado %d.%d, obtido %d.%d',
            $label,
            $expectedGate,
            $expectedLine,
            $actualGate,
            $actualLine
        ));
    }
}

$mapper = new ActivationMapper();
$knownSun = $mapper->map('SUN', 6.5764777);
requireActivation($knownSun->gate, $knownSun->line, 17, 3, 'SUN conhecido');

$provider = new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
);
$utc = new DateTimeImmutable('1982-03-27 14:05:00', new DateTimeZone('UTC'));
$expected = [
    'SUN' => [17, 3],
    'EARTH' => [18, 3],
    'MOON' => [27, 4],
    'NORTH_NODE' => [53, 5],
    'SOUTH_NODE' => [54, 5],
    'MERCURY' => [22, 6],
    'VENUS' => [49, 2],
    'MARS' => [48, 3],
    'JUPITER' => [44, 2],
    'SATURN' => [57, 6],
    'URANUS' => [34, 5],
    'NEPTUNE' => [11, 5],
    'PLUTO' => [32, 6],
];

foreach ($expected as $body => [$gate, $line]) {
    $longitude = $provider->longitude($utc, $body);
    $activation = $mapper->map($body, $longitude);
    requireActivation($activation->gate, $activation->line, $gate, $line, $body);

    if ($activation->color < 1 || $activation->color > 6
        || $activation->tone < 1 || $activation->tone > 6
        || $activation->base < 1 || $activation->base > 5) {
        throw new RuntimeException("{$body}: subdivisão fora do intervalo válido");
    }
}

echo "Activation Mapper test OK\n";
