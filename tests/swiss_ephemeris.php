<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\SwissEphemerisProvider;

function requireClose(float $actual, float $expected, float $tolerance, string $label): void
{
    if (abs($actual - $expected) > $tolerance) {
        throw new RuntimeException(
            sprintf('%s: esperado %.7f, obtido %.7f', $label, $expected, $actual)
        );
    }
}

function requireSameValue(mixed $actual, mixed $expected, string $label): void
{
    if ($actual !== $expected) {
        throw new RuntimeException(
            sprintf('%s: valor inesperado', $label)
        );
    }
}

function normalizeLongitude(float $longitude): float
{
    $longitude = fmod($longitude, 360.0);

    return $longitude < 0.0 ? $longitude + 360.0 : $longitude;
}

$provider = new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
);
$utc = new DateTimeImmutable('1982-03-27 14:05:00', new DateTimeZone('UTC'));
$tolerance = 0.00001;

$sun = $provider->longitude($utc, 'SUN');
$northNode = $provider->longitude($utc, 'NORTH_NODE');
$earth = $provider->longitude($utc, 'EARTH');
$southNode = $provider->longitude($utc, 'SOUTH_NODE');

requireClose($sun, 6.5764777, $tolerance, 'SUN');
requireClose($northNode, 108.9629781, $tolerance, 'NORTH_NODE');
requireClose($earth, normalizeLongitude($sun + 180.0), $tolerance, 'EARTH');
requireClose(
    $southNode,
    normalizeLongitude($northNode + 180.0),
    $tolerance,
    'SOUTH_NODE'
);
requireSameValue($provider->isReliable(), true, 'isReliable');
requireSameValue($provider->name(), 'swiss-ephemeris-swetest', 'name');

echo "Swiss Ephemeris test OK\n";
