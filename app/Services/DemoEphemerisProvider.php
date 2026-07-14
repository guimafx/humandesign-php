<?php

declare(strict_types=1);

namespace App\Services;

final class DemoEphemerisProvider implements EphemerisProviderInterface
{
    public function longitude(
        \DateTimeImmutable $utc,
        string $celestialBody,
        ?float $latitude = null,
        ?float $longitude = null
    ): float {
        $seed = crc32($utc->format('Y-m-d H:i:s') . '|' . $celestialBody);
        return fmod((float) $seed / 1000.0, 360.0);
    }

    public function isReliable(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'demo-non-astronomical';
    }
}
