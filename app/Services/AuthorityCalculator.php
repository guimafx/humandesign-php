<?php

declare(strict_types=1);

namespace App\Services;

final class AuthorityCalculator
{
    private const HIERARCHY = [
        'Solar Plexus' => ['id' => 'emotional', 'name' => 'Emotional Solar Plexus'],
        'Sacral' => ['id' => 'sacral', 'name' => 'Sacral'],
        'Spleen' => ['id' => 'splenic', 'name' => 'Splenic'],
        'Ego' => ['id' => 'ego', 'name' => 'Ego'],
        'G' => ['id' => 'self_projected', 'name' => 'Self Projected'],
    ];

    public function calculate(array $definedCenters): array
    {
        $defined = array_fill_keys($definedCenters, true);

        foreach (self::HIERARCHY as $center => $authority) {
            if (isset($defined[$center])) {
                return $authority;
            }
        }

        if (isset($defined['Ajna'])) {
            return ['id' => 'mental', 'name' => 'Mental (Environmental)'];
        }

        return ['id' => 'lunar', 'name' => 'Lunar'];
    }
}
