<?php

declare(strict_types=1);

namespace App\Services;

final class DesignDateCalculator
{
    private const INITIAL_INTERVAL_DAYS = 88;
    private const DEFAULT_SOLAR_ARC_DEGREES = 88.0;
    private const SEARCH_STEP_DAYS = 2;
    private const TIME_TOLERANCE_SECONDS = 1;
    private const ANGULAR_TOLERANCE_DEGREES = 0.000001;
    private const MAX_SEARCH_ITERATIONS = 100;
    private const MAX_BISECTION_ITERATIONS = 100;

    public function __construct(
        private readonly EphemerisProviderInterface $ephemeris
    ) {
    }

    public function calculate(
        \DateTimeImmutable $birthUtc,
        float $solarArcDegrees = 88.0
    ): \DateTimeImmutable {
        if (!is_finite($solarArcDegrees)
            || $solarArcDegrees <= 0.0
            || $solarArcDegrees >= 360.0) {
            throw new \InvalidArgumentException(
                'O arco solar deve ser maior que 0 e menor que 360 graus.'
            );
        }

        $utcZone = new \DateTimeZone('UTC');
        $birthUtc = $birthUtc->setTimezone($utcZone);
        $natalSun = $this->ephemeris->longitude($birthUtc, 'SUN');
        $target = $this->normalize($natalSun - $solarArcDegrees);
        $estimatedDays = self::INITIAL_INTERVAL_DAYS
            * ($solarArcDegrees / self::DEFAULT_SOLAR_ARC_DEGREES);
        $estimate = $birthUtc->modify(sprintf('-%F days', $estimatedDays));
        $estimateDifference = $this->signedAngularDifference(
            $this->ephemeris->longitude($estimate, 'SUN'),
            $target
        );

        if (abs($estimateDifference) <= self::ANGULAR_TOLERANCE_DEGREES) {
            return $estimate;
        }

        $lower = $estimate;
        $upper = $estimate;
        $lowerDifference = $estimateDifference;
        $upperDifference = $estimateDifference;
        $bracketed = false;

        for ($iteration = 0; $iteration < self::MAX_SEARCH_ITERATIONS; $iteration++) {
            if ($lowerDifference > 0.0) {
                $lower = $lower->modify('-' . self::SEARCH_STEP_DAYS . ' days');
                $lowerDifference = $this->signedAngularDifference(
                    $this->ephemeris->longitude($lower, 'SUN'),
                    $target
                );
            } else {
                $candidate = $upper->modify('+' . self::SEARCH_STEP_DAYS . ' days');
                $upper = $candidate > $birthUtc ? $birthUtc : $candidate;
                $upperDifference = $this->signedAngularDifference(
                    $this->ephemeris->longitude($upper, 'SUN'),
                    $target
                );
            }

            if ($lowerDifference <= 0.0 && $upperDifference >= 0.0) {
                $bracketed = true;
                break;
            }

            if ($upper == $birthUtc && $upperDifference < 0.0) {
                break;
            }
        }

        if (!$bracketed) {
            throw new \RuntimeException(
                'Não foi possível localizar um intervalo para a data Design.'
            );
        }

        $lowerTimestamp = $lower->getTimestamp();
        $upperTimestamp = $upper->getTimestamp();

        for ($iteration = 0; $iteration < self::MAX_BISECTION_ITERATIONS; $iteration++) {
            if ($upperTimestamp - $lowerTimestamp <= self::TIME_TOLERANCE_SECONDS) {
                return (new \DateTimeImmutable('@' . (string) round(
                    ($lowerTimestamp + $upperTimestamp) / 2
                )))->setTimezone($utcZone);
            }

            $middleTimestamp = intdiv($lowerTimestamp + $upperTimestamp, 2);
            $middle = (new \DateTimeImmutable('@' . $middleTimestamp))->setTimezone($utcZone);
            $difference = $this->signedAngularDifference(
                $this->ephemeris->longitude($middle, 'SUN'),
                $target
            );

            if (abs($difference) <= self::ANGULAR_TOLERANCE_DEGREES) {
                return $middle;
            }

            if ($difference < 0.0) {
                $lowerTimestamp = $middleTimestamp;
            } else {
                $upperTimestamp = $middleTimestamp;
            }
        }

        throw new \RuntimeException(
            'O cálculo da data Design não convergiu dentro do limite de iterações.'
        );
    }

    private function signedAngularDifference(float $angle, float $reference): float
    {
        $difference = $this->normalize($angle - $reference + 180.0) - 180.0;

        return $difference === -180.0 ? 180.0 : $difference;
    }

    private function normalize(float $degrees): float
    {
        $degrees = fmod($degrees, 360.0);

        return $degrees < 0.0 ? $degrees + 360.0 : $degrees;
    }
}
