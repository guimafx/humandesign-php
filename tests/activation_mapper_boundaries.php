<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\ActivationMapper;

function requireMapped(
    ActivationMapper $mapper,
    float $longitude,
    int $gate,
    int $line,
    string $label
): void {
    $activation = $mapper->map('TEST', $longitude);

    if ($activation->gate !== $gate || $activation->line !== $line) {
        throw new RuntimeException(sprintf(
            '%s (%.12f°): esperado %d.%d, obtido %d.%d',
            $label,
            $longitude,
            $gate,
            $line,
            $activation->gate,
            $activation->line
        ));
    }

    if ($activation->line < 1 || $activation->line > 6
        || $activation->color < 1 || $activation->color > 6
        || $activation->tone < 1 || $activation->tone > 6
        || $activation->base < 1 || $activation->base > 5) {
        throw new RuntimeException("{$label}: subdivisão fora do intervalo válido");
    }
}

$mapper = new ActivationMapper();
$epsilon = 0.000000001;
$gateSize = 360.0 / 64.0;
$lineSize = $gateSize / 6.0;
$raveStart = 302.0;

$gateBoundary = $raveStart + $gateSize;
requireMapped($mapper, $gateBoundary - $epsilon, 41, 6, 'antes da fronteira de gate');
requireMapped($mapper, $gateBoundary, 19, 1, 'na fronteira de gate');
requireMapped($mapper, $gateBoundary + $epsilon, 19, 1, 'depois da fronteira de gate');

$lineBoundary = $raveStart + $lineSize;
requireMapped($mapper, $lineBoundary - $epsilon, 41, 1, 'antes da fronteira de linha');
requireMapped($mapper, $lineBoundary, 41, 2, 'na fronteira de linha');
requireMapped($mapper, $lineBoundary + $epsilon, 41, 2, 'depois da fronteira de linha');

requireMapped($mapper, 0.0, 25, 2, 'zero');
requireMapped($mapper, 359.999999, 25, 2, 'imediatamente antes de 360');
requireMapped($mapper, 360.0, 25, 2, '360 normalizado');
requireMapped($mapper, -1.0, 25, 1, 'longitude negativa');
requireMapped($mapper, 662.0, 41, 1, 'longitude acima de 360');

echo "Activation Mapper boundary test OK\n";
