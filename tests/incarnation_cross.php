<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\IncarnationCrossCalculator;

$calculator = new IncarnationCrossCalculator();
$cross = $calculator->calculate(17, 18, 58, 52);

if ($cross['gates'] !== [17, 18, 58, 52]
    || $cross['status'] !== 'unresolved'
    || $cross['quarter'] !== null
    || $cross['angle'] !== null
    || $cross['name'] !== null) {
    throw new RuntimeException('Estrutura-base da Cruz de Encarnação divergente.');
}

foreach ([0, 65] as $invalidGate) {
    try {
        $calculator->calculate($invalidGate, 18, 58, 52);
        throw new RuntimeException('Gate inválido não lançou InvalidArgumentException.');
    } catch (InvalidArgumentException) {
    }
}

echo "Incarnation Cross test OK\n";
