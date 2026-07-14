<?php

declare(strict_types=1);

namespace App\Services;

interface EphemerisProviderInterface
{
    public function longitude(
        \DateTimeImmutable $utc,
        string $celestialBody,
        ?float $latitude = null,
        ?float $longitude = null
    ): float;

    public function isReliable(): bool;

    public function name(): string;
}
