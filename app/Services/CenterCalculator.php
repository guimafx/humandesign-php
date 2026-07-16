<?php

declare(strict_types=1);

namespace App\Services;

final class CenterCalculator
{
    public function __construct(private readonly ChannelCalculator $channelCalculator)
    {
    }

    public function calculate(array $activeChannels): array
    {
        $centers = [];

        foreach ($this->channelCalculator->centerConnections($activeChannels) as [$centerA, $centerB]) {
            $centers[$centerA] = true;
            $centers[$centerB] = true;
        }

        return array_keys($centers);
    }
}
