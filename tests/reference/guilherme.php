<?php

declare(strict_types=1);

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;
use App\Services\SwissEphemerisProvider;

return static function (): array {
    $birth = BirthData::fromArray([
        'name' => 'Guilherme Borges Viana',
        'date' => '1982-03-27',
        'time' => '14:05',
        'timezone' => 'UTC',
        'latitude' => '',
        'longitude' => '',
    ]);

    $chart = (new HumanDesignCalculator(new SwissEphemerisProvider(
        '/usr/local/bin/swetest',
        '/usr/local/share/swisseph/ephe'
    )))->calculate($birth);

    return [
        'chart' => $chart,
        'expected' => [
            'type' => ['id' => 'generator', 'name' => 'Generator'],
            'authority' => ['id' => 'emotional', 'name' => 'Emotional Solar Plexus'],
            'definition' => ['id' => 'split', 'name' => 'Split Definition'],
        ],
    ];
};
