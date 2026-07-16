<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;
use App\Services\SwissEphemerisProvider;

function requireChartActivations(array $actual, array $expected, string $side): void
{
    if (count($actual) !== 13 || array_keys($actual) !== array_keys($expected)) {
        throw new RuntimeException("{$side} não contém exatamente os 13 corpos esperados.");
    }

    foreach ($expected as $body => [$gate, $line]) {
        if ($actual[$body]['gate'] !== $gate || $actual[$body]['line'] !== $line) {
            throw new RuntimeException(sprintf(
                '%s %s: esperado %d.%d, obtido %d.%d',
                $side,
                $body,
                $gate,
                $line,
                $actual[$body]['gate'],
                $actual[$body]['line']
            ));
        }
    }
}

$personalityExpected = [
    'SUN' => [17, 3], 'EARTH' => [18, 3], 'MOON' => [27, 4],
    'NORTH_NODE' => [53, 5], 'SOUTH_NODE' => [54, 5], 'MERCURY' => [22, 6],
    'VENUS' => [49, 2], 'MARS' => [48, 3], 'JUPITER' => [44, 2],
    'SATURN' => [57, 6], 'URANUS' => [34, 5], 'NEPTUNE' => [11, 5],
    'PLUTO' => [32, 6],
];
$designExpected = [
    'SUN' => [58, 6], 'EARTH' => [52, 6], 'MOON' => [49, 4],
    'NORTH_NODE' => [62, 2], 'SOUTH_NODE' => [61, 2], 'MERCURY' => [54, 6],
    'VENUS' => [19, 2], 'MARS' => [18, 3], 'JUPITER' => [28, 5],
    'SATURN' => [32, 1], 'URANUS' => [34, 3], 'NEPTUNE' => [11, 3],
    'PLUTO' => [50, 1],
];
$birth = BirthData::fromArray([
    'name' => 'Full Chart Reference', 'date' => '1982-03-27',
    'time' => '14:05', 'timezone' => 'UTC', 'latitude' => '', 'longitude' => '',
]);
$result = (new HumanDesignCalculator(new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
)))->calculate($birth);

requireChartActivations($result['personality'], $personalityExpected, 'Personality');
requireChartActivations($result['design'], $designExpected, 'Design');

if (!isset($result['design_date'])
    || (new DateTimeImmutable($result['design_date']))->getOffset() !== 0
    || !str_ends_with($result['design_date'], '+00:00')) {
    throw new RuntimeException('design_date ausente ou fora de UTC.');
}

$expectedGates = [];
foreach ([$personalityExpected, $designExpected] as $side) {
    foreach ($side as [$gate]) {
        $expectedGates[$gate] = true;
    }
}

if ($result['active_gates'] !== array_keys($expectedGates)) {
    throw new RuntimeException('active_gates não corresponde à união ordenada sem duplicação.');
}

$expectedChannels = ['17-62', '18-58', '19-49', '27-50', '32-54', '34-57'];
if ($result['active_channels'] !== $expectedChannels) {
    throw new RuntimeException('Canais completos divergentes: ' . implode(', ', $result['active_channels']));
}

$expectedCenters = ['Ajna', 'Throat', 'Spleen', 'Root', 'Solar Plexus', 'Sacral'];
if ($result['defined_centers'] !== $expectedCenters) {
    throw new RuntimeException('Centros definidos divergentes: ' . implode(', ', $result['defined_centers']));
}

if ($result['profile']['value'] !== '3/6'
    || $result['profile']['personality_line'] !== 3
    || $result['profile']['design_line'] !== 6) {
    throw new RuntimeException('Perfil divergente.');
}

if ($result['incarnation_cross']['gates'] !== [17, 18, 58, 52]
    || $result['incarnation_cross']['status'] !== 'unresolved') {
    throw new RuntimeException('Estrutura-base da Cruz de Encarnação divergente.');
}

if ($result['metadata']['reliable'] !== true
    || $result['metadata']['ephemeris'] !== 'swiss-ephemeris-swetest') {
    throw new RuntimeException('Metadados do Swiss Ephemeris divergentes.');
}

echo "Full Chart test OK\n";
