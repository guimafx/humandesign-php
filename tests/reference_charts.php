<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';
require __DIR__ . '/support/ReferenceChartLoader.php';

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;
use App\Services\SwissEphemerisProvider;

function referenceFailure(string $id, string $field, mixed $expected, mixed $actual): never
{
    throw new RuntimeException(sprintf(
        '%s: divergência em %s; esperado %s, obtido %s.',
        $id,
        $field,
        var_export($expected, true),
        var_export($actual, true)
    ));
}

function compareActivations(string $id, string $side, array $expected, mixed $actual): void
{
    if (!is_array($actual)) {
        referenceFailure($id, $side, $expected, $actual);
    }

    foreach ($expected as $body => $expectedActivation) {
        if (!is_array($expectedActivation)
            || !array_key_exists('gate', $expectedActivation)
            || !array_key_exists('line', $expectedActivation)) {
            throw new RuntimeException("{$id}: {$side}.{$body} deve informar gate e line.");
        }
        $actualActivation = $actual[$body] ?? null;
        if (!is_array($actualActivation)) {
            referenceFailure($id, "{$side}.{$body}", $expectedActivation, $actualActivation);
        }

        if (($actualActivation['body'] ?? null) !== $body) {
            throw new RuntimeException(sprintf(
                '%s: %s %s; corpo esperado %s, obtido %s.',
                $id,
                $side,
                $body,
                var_export($body, true),
                var_export($actualActivation['body'] ?? null, true)
            ));
        }

        foreach (['gate', 'line'] as $part) {
            if (($actualActivation[$part] ?? null) !== $expectedActivation[$part]) {
                throw new RuntimeException(sprintf(
                    '%s: %s %s; esperado %s, obtido %s.',
                    $id,
                    $side,
                    $body,
                    var_export($expectedActivation, true),
                    var_export([
                        'gate' => $actualActivation['gate'] ?? null,
                        'line' => $actualActivation['line'] ?? null,
                    ], true)
                ));
            }
        }
    }
}

$collections = (new ReferenceChartLoader())->loadAll();
if ($collections['active'] === []) {
    throw new RuntimeException('Nenhuma fixture de referência active encontrada.');
}

$calculator = new HumanDesignCalculator(new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
));
$scalarPaths = [
    'type' => ['type', 'id'],
    'authority' => ['authority', 'id'],
    'definition' => ['definition', 'id'],
    'profile' => ['profile', 'value'],
];

foreach ($collections['active'] as $fixture) {
    $id = $fixture['id'];
    $chart = $calculator->calculate(BirthData::fromArray([
        'name' => $fixture['label'],
        ...$fixture['birth'],
    ]));

    foreach ($fixture['expected'] as $field => $expected) {
        if (isset($scalarPaths[$field])) {
            $actual = $chart;
            foreach ($scalarPaths[$field] as $segment) {
                $actual = is_array($actual) && array_key_exists($segment, $actual)
                    ? $actual[$segment]
                    : null;
            }
            if ($actual !== $expected) {
                referenceFailure($id, $field, $expected, $actual);
            }
            continue;
        }

        if ($field === 'active_channels' || $field === 'defined_centers') {
            $actual = $chart[$field] ?? null;
            if ($actual !== $expected) {
                referenceFailure($id, $field, $expected, $actual);
            }
            continue;
        }

        if ($field === 'personality' || $field === 'design') {
            compareActivations($id, $field, $expected, $chart[$field] ?? null);
            continue;
        }

        throw new RuntimeException("{$id}: campo expected não suportado: {$field}.");
    }

    echo "Reference active: {$id} OK\n";
}

$pendingById = [];
foreach ($collections['pending'] as $fixture) {
    $pendingById[$fixture['id']] = true;
}
foreach (['manifesting-generator-001', 'projector-001', 'manifestor-001', 'reflector-001'] as $id) {
    if (isset($pendingById[$id])) {
        echo "Reference pending: {$id}\n";
        unset($pendingById[$id]);
    }
}
foreach (array_keys($pendingById) as $id) {
    echo "Reference pending: {$id}\n";
}

echo "Reference Charts test OK\n";
