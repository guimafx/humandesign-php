<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Services\DesignDateCalculator;
use App\Services\SwissEphemerisProvider;

function normalizeDesignTest(float $degrees): float
{
    $degrees = fmod($degrees, 360.0);

    return $degrees < 0.0 ? $degrees + 360.0 : $degrees;
}

$provider = new SwissEphemerisProvider(
    '/usr/local/bin/swetest',
    '/usr/local/share/swisseph/ephe'
);
$birth = new DateTimeImmutable('1982-03-27 14:05:00', new DateTimeZone('UTC'));
$reference = new DateTimeImmutable('1981-12-30 08:47:00', new DateTimeZone('UTC'));
$designDate = (new DesignDateCalculator($provider))->calculate($birth);
$secondsDifference = abs($designDate->getTimestamp() - $reference->getTimestamp());

if ($secondsDifference > 120) {
    throw new RuntimeException(sprintf(
        'Data Design difere %d segundos da referência: %s',
        $secondsDifference,
        $designDate->format(DATE_ATOM)
    ));
}

$solarArc = normalizeDesignTest(
    $provider->longitude($birth, 'SUN')
    - $provider->longitude($designDate, 'SUN')
);

if (abs($solarArc - 88.0) > 0.00002) {
    throw new RuntimeException(sprintf(
        'Arco solar esperado 88°, obtido %.10f°',
        $solarArc
    ));
}

echo "Design Date Calculator test OK\n";
