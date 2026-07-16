<?php

declare(strict_types=1);

return [
    'status' => 'active',
    'id' => 'generator-emotional-001',
    'label' => 'Guilherme — Generator Emotional Split 3/6',
    'birth' => [
        'date' => '1982-03-27',
        'time' => '11:05',
        'timezone' => 'America/Sao_Paulo',
        'latitude' => null,
        'longitude' => null,
    ],
    'expected' => [
        'type' => 'generator',
        'authority' => 'emotional',
        'definition' => 'split',
        'profile' => '3/6',
        'active_channels' => ['17-62', '18-58', '19-49', '27-50', '32-54', '34-57'],
        'defined_centers' => ['Ajna', 'Throat', 'Spleen', 'Root', 'Solar Plexus', 'Sacral'],
        'personality' => [
            'SUN' => ['gate' => 17, 'line' => 3], 'EARTH' => ['gate' => 18, 'line' => 3],
            'MOON' => ['gate' => 27, 'line' => 4], 'NORTH_NODE' => ['gate' => 53, 'line' => 5],
            'SOUTH_NODE' => ['gate' => 54, 'line' => 5], 'MERCURY' => ['gate' => 22, 'line' => 6],
            'VENUS' => ['gate' => 49, 'line' => 2], 'MARS' => ['gate' => 48, 'line' => 3],
            'JUPITER' => ['gate' => 44, 'line' => 2], 'SATURN' => ['gate' => 57, 'line' => 6],
            'URANUS' => ['gate' => 34, 'line' => 5], 'NEPTUNE' => ['gate' => 11, 'line' => 5],
            'PLUTO' => ['gate' => 32, 'line' => 6],
        ],
        'design' => [
            'SUN' => ['gate' => 58, 'line' => 6], 'EARTH' => ['gate' => 52, 'line' => 6],
            'MOON' => ['gate' => 49, 'line' => 4], 'NORTH_NODE' => ['gate' => 62, 'line' => 2],
            'SOUTH_NODE' => ['gate' => 61, 'line' => 2], 'MERCURY' => ['gate' => 54, 'line' => 6],
            'VENUS' => ['gate' => 19, 'line' => 2], 'MARS' => ['gate' => 18, 'line' => 3],
            'JUPITER' => ['gate' => 28, 'line' => 5], 'SATURN' => ['gate' => 32, 'line' => 1],
            'URANUS' => ['gate' => 34, 'line' => 3], 'NEPTUNE' => ['gate' => 11, 'line' => 3],
            'PLUTO' => ['gate' => 50, 'line' => 1],
        ],
    ],
    'source' => [
        'provider' => 'Swiss Ephemeris / referência visual previamente validada',
        'reference' => 'docs/VALIDATION.md',
        'checked_at' => '2026-07-16',
    ],
    'privacy' => [
        'consent' => true,
        'anonymized' => false,
    ],
];
