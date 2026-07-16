<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\ProfileCalculator;

$calculator = new ProfileCalculator();

if ($calculator->calculate(3, 6) !== [
    'value' => '3/6',
    'personality_line' => 3,
    'design_line' => 6,
]) {
    throw new RuntimeException('Perfil 3/6 divergente.');
}

if ($calculator->calculate(1, 6)['value'] !== '1/6') {
    throw new RuntimeException('Limites válidos de linha não foram aceitos.');
}

foreach ([[0, 1], [7, 1], [1, 0], [1, 7]] as [$personalityLine, $designLine]) {
    try {
        $calculator->calculate($personalityLine, $designLine);
        throw new RuntimeException('Linha inválida não lançou InvalidArgumentException.');
    } catch (InvalidArgumentException) {
    }
}

echo "Profile test OK\n";
