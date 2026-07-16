<?php

declare(strict_types=1);

namespace App\Services;

final class DefinitionCalculator
{
    private const DEFINITIONS = [
        0 => ['id' => 'none', 'name' => 'No Definition'],
        1 => ['id' => 'single', 'name' => 'Single Definition'],
        2 => ['id' => 'split', 'name' => 'Split Definition'],
        3 => ['id' => 'triple_split', 'name' => 'Triple Split'],
        4 => ['id' => 'quadruple_split', 'name' => 'Quadruple Split'],
    ];

    public function __construct(private readonly ChannelCalculator $channelCalculator)
    {
    }

    public function calculate(array $definedCenters, array $activeChannels): array
    {
        $graph = array_fill_keys($definedCenters, []);

        foreach ($this->channelCalculator->centerConnections($activeChannels) as [$centerA, $centerB]) {
            $graph[$centerA][$centerB] = true;
            $graph[$centerB][$centerA] = true;
        }

        $components = $this->countComponents($graph);

        if (!isset(self::DEFINITIONS[$components])) {
            throw new \LogicException("Quantidade impossível de componentes definidos: {$components}");
        }

        return self::DEFINITIONS[$components];
    }

    private function countComponents(array $graph): int
    {
        $visited = [];
        $components = 0;

        foreach (array_keys($graph) as $start) {
            if (isset($visited[$start])) {
                continue;
            }

            $components++;
            $stack = [$start];

            while ($stack !== []) {
                $center = array_pop($stack);
                if (isset($visited[$center])) {
                    continue;
                }

                $visited[$center] = true;
                foreach (array_keys($graph[$center]) as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $stack[] = $neighbor;
                    }
                }
            }
        }

        return $components;
    }
}
