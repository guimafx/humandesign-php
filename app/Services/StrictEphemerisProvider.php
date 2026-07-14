<?php

declare(strict_types=1);

namespace App\Services;

final class StrictEphemerisProvider implements EphemerisProviderInterface
{
    public function longitude(
        \DateTimeImmutable $utc,
        string $celestialBody,
        ?float $latitude = null,
        ?float $longitude = null
    ): float {
        throw new \RuntimeException(
            'Nenhum provedor real de efemérides foi configurado. ' .
            'Use EPHEMERIS_DRIVER=demo somente para testar a interface.'
        );
    }

    public function isReliable(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'strict-unconfigured';
    }
}
