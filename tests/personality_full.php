<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;
use App\Services\SwissEphemerisProvider;

$birth = BirthData::fromArray([
    'name' => 'Personality Reference',
    'date' => '1982-03-27',
    'time' => '14:05',
    'timezone' => 'UTC',
    'latitude' => '',
    'longitude' => '',
]);
$calculator = new HumanDesignCalculator(new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
));
$result = $calculator->calculate($birth);
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

if (array_keys($result['personality']) !== array_keys($expected)) {
    throw new RuntimeException('Personality não contém exatamente os corpos esperados.');
}

foreach ($expected as $body => [$expectedGate, $expectedLine]) {
    $activation = $result['personality'][$body];

    if ($activation['gate'] !== $expectedGate
        || $activation['line'] !== $expectedLine) {
        throw new RuntimeException(sprintf(
            '%s: esperado %d.%d, obtido %d.%d',
            $body,
            $expectedGate,
            $expectedLine,
            $activation['gate'],
            $activation['line']
        ));
    }
}

$expectedActiveGates = array_values(array_unique(array_map(
    static fn (array $activation): int => $activation[0],
    $expected
)));

if ($result['active_gates'] !== $expectedActiveGates) {
    throw new RuntimeException(sprintf(
        'active_gates: esperado [%s], obtido [%s]',
        implode(', ', $expectedActiveGates),
        implode(', ', $result['active_gates'])
    ));
}

echo "Full Personality test OK\n";
