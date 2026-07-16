<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\ChartIdentityCalculator;

$calculator = new ChartIdentityCalculator();
$cases = [
    'generator' => [
        'strategy' => ['id' => 'wait_to_respond', 'name' => 'Wait to Respond'],
        'signature' => ['id' => 'satisfaction', 'name' => 'Satisfaction'],
        'not_self_theme' => ['id' => 'frustration', 'name' => 'Frustration'],
    ],
    'manifesting_generator' => [
        'strategy' => ['id' => 'wait_to_respond', 'name' => 'Wait to Respond'],
        'signature' => ['id' => 'satisfaction', 'name' => 'Satisfaction'],
        'not_self_theme' => ['id' => 'frustration', 'name' => 'Frustration'],
    ],
    'projector' => [
        'strategy' => ['id' => 'wait_for_invitation', 'name' => 'Wait for Invitation'],
        'signature' => ['id' => 'success', 'name' => 'Success'],
        'not_self_theme' => ['id' => 'bitterness', 'name' => 'Bitterness'],
    ],
    'manifestor' => [
        'strategy' => ['id' => 'inform_before_acting', 'name' => 'Inform Before Acting'],
        'signature' => ['id' => 'peace', 'name' => 'Peace'],
        'not_self_theme' => ['id' => 'anger', 'name' => 'Anger'],
    ],
    'reflector' => [
        'strategy' => ['id' => 'wait_a_lunar_cycle', 'name' => 'Wait a Lunar Cycle'],
        'signature' => ['id' => 'surprise', 'name' => 'Surprise'],
        'not_self_theme' => ['id' => 'disappointment', 'name' => 'Disappointment'],
    ],
];

foreach ($cases as $typeId => $expected) {
    $actual = $calculator->calculate(['id' => $typeId, 'name' => 'ignored']);

    if ($actual !== $expected) {
        throw new RuntimeException("Chart Identity divergente para o tipo {$typeId}.");
    }
}

echo "Chart Identity test OK\n";
