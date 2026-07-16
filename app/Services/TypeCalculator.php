<?php

declare(strict_types=1);

namespace App\Services;

final class TypeCalculator
{
    private const MOTORS = ['Root', 'Sacral', 'Solar Plexus', 'Ego'];

    public function __construct(private readonly ChannelCalculator $channelCalculator)
    {
    }

    public function calculate(array $definedCenters, array $activeChannels): array
    {
        if ($definedCenters === []) {
            return ['id' => 'reflector', 'name' => 'Reflector'];
        }

        $defined = array_fill_keys($definedCenters, true);
        $motorConnectedToThroat = $this->motorConnectsToThroat($defined, $activeChannels);

        if (isset($defined['Sacral'])) {
            return $motorConnectedToThroat
                ? ['id' => 'manifesting_generator', 'name' => 'Manifesting Generator']
                : ['id' => 'generator', 'name' => 'Generator'];
        }

        return $motorConnectedToThroat
            ? ['id' => 'manifestor', 'name' => 'Manifestor']
            : ['id' => 'projector', 'name' => 'Projector'];
    }

    private function motorConnectsToThroat(array $defined, array $activeChannels): bool
    {
        if (!isset($defined['Throat'])) {
            return false;
        }

        $graph = array_fill_keys(array_keys($defined), []);
        foreach ($this->channelCalculator->centerConnections($activeChannels) as [$centerA, $centerB]) {
            $graph[$centerA][$centerB] = true;
            $graph[$centerB][$centerA] = true;
        }

        $visited = [];
        $stack = ['Throat'];

        while ($stack !== []) {
            $center = array_pop($stack);
            if (isset($visited[$center])) {
                continue;
            }

            $visited[$center] = true;
            if (in_array($center, self::MOTORS, true)) {
                return true;
            }

            foreach (array_keys($graph[$center]) as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $stack[] = $neighbor;
                }
            }
        }

        return false;
    }
}
