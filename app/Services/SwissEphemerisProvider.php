<?php

declare(strict_types=1);

namespace App\Services;

final class SwissEphemerisProvider implements EphemerisProviderInterface
{
    private const BODY_SELECTORS = [
        'SUN' => '0',
        'MOON' => '1',
        'MERCURY' => '2',
        'VENUS' => '3',
        'MARS' => '4',
        'JUPITER' => '5',
        'SATURN' => '6',
        'URANUS' => '7',
        'NEPTUNE' => '8',
        'PLUTO' => '9',
        'NORTH_NODE' => 't',
    ];

    public function __construct(
        private readonly string $swetestBin,
        private readonly string $ephemerisPath
    ) {
        if (!is_file($this->swetestBin)) {
            throw new \RuntimeException(
                "Executável swetest não encontrado: {$this->swetestBin}"
            );
        }

        if (!is_executable($this->swetestBin)) {
            throw new \RuntimeException(
                "Executável swetest sem permissão de execução: {$this->swetestBin}"
            );
        }

        if (!is_dir($this->ephemerisPath)) {
            throw new \RuntimeException(
                "Pasta de efemérides não encontrada: {$this->ephemerisPath}"
            );
        }

        if (!is_readable($this->ephemerisPath)) {
            throw new \RuntimeException(
                "Pasta de efemérides sem permissão de leitura: {$this->ephemerisPath}"
            );
        }
    }

    public function longitude(
        \DateTimeImmutable $utc,
        string $celestialBody,
        ?float $latitude = null,
        ?float $longitude = null
    ): float {
        $celestialBody = strtoupper($celestialBody);

        if ($celestialBody === 'EARTH') {
            return $this->normalize($this->longitude($utc, 'SUN') + 180.0);
        }

        if ($celestialBody === 'SOUTH_NODE') {
            return $this->normalize($this->longitude($utc, 'NORTH_NODE') + 180.0);
        }

        $selector = self::BODY_SELECTORS[$celestialBody] ?? null;

        if ($selector === null) {
            throw new \RuntimeException(
                "Corpo celeste não suportado pelo Swiss Ephemeris: {$celestialBody}"
            );
        }

        if (!function_exists('proc_open')) {
            throw new \RuntimeException('A função proc_open não está disponível.');
        }

        $utc = $utc->setTimezone(new \DateTimeZone('UTC'));
        $command = [
            $this->swetestBin,
            '-edir' . $this->ephemerisPath,
            '-b' . $utc->format('j.n.Y'),
            '-utc' . $utc->format('H:i:s'),
            '-p' . $selector,
            '-eswe',
            '-fPl',
            '-g,',
            '-head',
        ];
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        $process = proc_open($command, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Não foi possível iniciar o processo swetest.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            $details = trim($stderr === false ? '' : $stderr);
            throw new \RuntimeException(
                "swetest encerrou com código {$exitCode}" .
                ($details === '' ? '.' : ": {$details}")
            );
        }

        if ($stdout === false || trim($stdout) === '') {
            throw new \RuntimeException('swetest retornou stdout vazio.');
        }

        $line = trim($stdout);
        $numericPattern = '[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[Ee][+-]?\d+)?';

        if (preg_match('/^[^,\r\n]+,\s*(' . $numericPattern . ')\s*$/D', $line, $matches) !== 1) {
            throw new \RuntimeException(
                "Saída inválida do swetest; longitude não numérica: {$line}"
            );
        }

        $result = (float) $matches[1];

        if (!is_finite($result)) {
            throw new \RuntimeException(
                "Saída inválida do swetest; longitude não numérica: {$line}"
            );
        }

        return $this->normalize($result);
    }

    public function isReliable(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'swiss-ephemeris-swetest';
    }

    private function normalize(float $longitude): float
    {
        $longitude = fmod($longitude, 360.0);

        return $longitude < 0.0 ? $longitude + 360.0 : $longitude;
    }
}
