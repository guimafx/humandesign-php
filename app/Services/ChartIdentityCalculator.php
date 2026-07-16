<?php

declare(strict_types=1);

namespace App\Services;

final class ChartIdentityCalculator
{
    private const IDENTITIES = [
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

    public function calculate(array $type): array
    {
        $typeId = $type['id'] ?? null;

        if (!is_string($typeId) || !isset(self::IDENTITIES[$typeId])) {
            throw new \InvalidArgumentException('Tipo inválido para calcular Chart Identity.');
        }

        return self::IDENTITIES[$typeId];
    }
}
