<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Activation;

final class ActivationMapper
{
    private const RAVE_ORDER = [
        41,19,13,49,30,55,37,63,
        22,36,25,17,21,51,42,3,
        27,24,2,23,8,20,16,35,
        45,12,15,52,39,53,62,56,
        31,33,7,4,29,59,40,64,
        47,6,46,18,48,57,32,50,
        28,44,1,43,14,34,9,5,
        26,11,10,58,38,54,61,60,
    ];

    // Valor provisório: validar contra charts conhecidos.
    private const RAVE_OFFSET_DEGREES = 0.0;

    public function map(string $body, float $longitude): Activation
    {
        $adjusted = $this->normalize($longitude - self::RAVE_OFFSET_DEGREES);

        $gateSize = 360.0 / 64.0;
        $lineSize = $gateSize / 6.0;
        $colorSize = $lineSize / 6.0;
        $toneSize = $colorSize / 6.0;
        $baseSize = $toneSize / 5.0;

        $gateIndex = min(63, (int) floor($adjusted / $gateSize));
        $gate = self::RAVE_ORDER[$gateIndex];

        $insideGate = fmod($adjusted, $gateSize);
        $line = min(6, (int) floor($insideGate / $lineSize) + 1);

        $insideLine = fmod($insideGate, $lineSize);
        $color = min(6, (int) floor($insideLine / $colorSize) + 1);

        $insideColor = fmod($insideLine, $colorSize);
        $tone = min(6, (int) floor($insideColor / $toneSize) + 1);

        $insideTone = fmod($insideColor, $toneSize);
        $base = min(5, (int) floor($insideTone / $baseSize) + 1);

        return new Activation(
            $body,
            $this->normalize($longitude),
            $gate,
            $line,
            $color,
            $tone,
            $base
        );
    }

    private function normalize(float $degrees): float
    {
        $degrees = fmod($degrees, 360.0);
        return $degrees < 0 ? $degrees + 360.0 : $degrees;
    }
}
