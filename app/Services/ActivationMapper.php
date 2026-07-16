<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Activation;

final class ActivationMapper
{
    private const CIRCLE_DEGREES = 360.0;
    private const GATE_COUNT = 64;
    private const LINES_PER_GATE = 6;
    private const COLORS_PER_LINE = 6;
    private const TONES_PER_COLOR = 6;
    private const BASES_PER_TONE = 5;

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

    /*
     * A sequência começa no limite inicial do portão 41, em 302° da
     * longitude eclíptica. Os portões avançam junto com a longitude zodiacal
     * (sentido anti-horário no círculo astronômico), portanto deslocamos esse
     * ponto para 0° subtraindo 302° e normalizando o resultado.
     */
    private const RAVE_START_LONGITUDE_DEGREES = 302.0;

    public function map(string $body, float $longitude): Activation
    {
        $adjusted = $this->normalize(
            $longitude - self::RAVE_START_LONGITUDE_DEGREES
        );

        $gateSize = self::CIRCLE_DEGREES / self::GATE_COUNT;
        $lineSize = $gateSize / self::LINES_PER_GATE;
        $colorSize = $lineSize / self::COLORS_PER_LINE;
        $toneSize = $colorSize / self::TONES_PER_COLOR;
        $baseSize = $toneSize / self::BASES_PER_TONE;

        $gateIndex = min(self::GATE_COUNT - 1, (int) floor($adjusted / $gateSize));
        $gate = self::RAVE_ORDER[$gateIndex];

        $insideGate = $adjusted - ($gateIndex * $gateSize);
        $lineIndex = min(
            self::LINES_PER_GATE - 1,
            (int) floor($insideGate / $lineSize)
        );
        $line = $lineIndex + 1;

        $insideLine = $insideGate - ($lineIndex * $lineSize);
        $colorIndex = min(
            self::COLORS_PER_LINE - 1,
            (int) floor($insideLine / $colorSize)
        );
        $color = $colorIndex + 1;

        $insideColor = $insideLine - ($colorIndex * $colorSize);
        $toneIndex = min(
            self::TONES_PER_COLOR - 1,
            (int) floor($insideColor / $toneSize)
        );
        $tone = $toneIndex + 1;

        $insideTone = $insideColor - ($toneIndex * $toneSize);
        $base = min(
            self::BASES_PER_TONE,
            (int) floor($insideTone / $baseSize) + 1
        );

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
        $degrees = fmod($degrees, self::CIRCLE_DEGREES);

        return $degrees < 0.0 ? $degrees + self::CIRCLE_DEGREES : $degrees;
    }
}
