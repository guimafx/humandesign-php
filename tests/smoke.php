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

assert(isset($result['metadata']));
assert(isset($result['personality']['SUN']));
assert(isset($result['design']['SUN']));
assert(isset($result['design_date']));
assert($result['metadata']['reliable'] === false);

echo "Smoke test OK\n";
