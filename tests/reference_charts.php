<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';
require __DIR__ . '/support/ReferenceChartLoader.php';

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;
use App\Services\SwissEphemerisProvider;

function assertReferenceValue(mixed $actual, mixed $expected, string $path, string $id): void
{
    if (is_array($expected)) {
        if (!is_array($actual)) {
            throw new RuntimeException("{$id}: {$path} deveria ser array.");
        }
        if (array_is_list($expected) && count($actual) !== count($expected)) {
            throw new RuntimeException(sprintf(
                '%s: divergência em %s; esperado %d itens, obtido %d.',
                $id,
                $path,
                count($expected),
                count($actual)
            ));
        }
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $actual)) {
                throw new RuntimeException("{$id}: campo calculado ausente: {$path}.{$key}.");
            }
            assertReferenceValue($actual[$key], $value, "{$path}.{$key}", $id);
        }
        return;
    }

    if ($actual !== $expected) {
        throw new RuntimeException(sprintf(
            '%s: divergência em %s; esperado %s, obtido %s.',
            $id,
            $path,
            var_export($expected, true),
            var_export($actual, true)
        ));
    }
}

$loader = new ReferenceChartLoader();
$fixtures = $loader->loadAll(__DIR__ . '/reference');
$calculator = new HumanDesignCalculator(new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
));
$paths = [
    'type' => ['type', 'id'],
    'authority' => ['authority', 'id'],
    'definition' => ['definition', 'id'],
    'profile' => ['profile', 'value'],
    'active_channels' => ['active_channels'],
    'defined_centers' => ['defined_centers'],
    'personality' => ['personality'],
    'design' => ['design'],
];

foreach ($fixtures as $fixture) {
    echo $fixture['id'] . "\n";
    $birth = BirthData::fromArray([
        'name' => $fixture['label'],
        ...$fixture['birth'],
    ]);
    $chart = $calculator->calculate($birth);

    foreach ($fixture['expected'] as $field => $expected) {
        if (!isset($paths[$field])) {
            throw new RuntimeException("{$fixture['id']}: campo expected não suportado: {$field}.");
        }
        $actual = $chart;
        foreach ($paths[$field] as $segment) {
            if (!array_key_exists($segment, $actual)) {
                throw new RuntimeException("{$fixture['id']}: campo calculado ausente: {$field}.");
            }
            $actual = $actual[$segment];
        }
        assertReferenceValue($actual, $expected, $field, $fixture['id']);
    }
}

echo "Reference Charts test OK\n";
