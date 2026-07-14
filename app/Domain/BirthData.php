<?php

declare(strict_types=1);

namespace App\Domain;

final class BirthData
{
    public function __construct(
        public readonly string $name,
        public readonly \DateTimeImmutable $localDateTime,
        public readonly string $timezone,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $name = trim((string) ($data['name'] ?? ''));
        $date = trim((string) ($data['date'] ?? ''));
        $time = trim((string) ($data['time'] ?? ''));
        $timezone = trim((string) ($data['timezone'] ?? ''));

        if ($name === '' || $date === '' || $time === '' || $timezone === '') {
            throw new \InvalidArgumentException('Nome, data, hora e timezone são obrigatórios.', 422);
        }

        try {
            $zone = new \DateTimeZone($timezone);
            $dateTime = new \DateTimeImmutable("{$date} {$time}:00", $zone);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Data, hora ou timezone inválido.', 422);
        }

        $lat = ($data['latitude'] ?? '') !== '' ? (float) $data['latitude'] : null;
        $lon = ($data['longitude'] ?? '') !== '' ? (float) $data['longitude'] : null;

        return new self($name, $dateTime, $timezone, $lat, $lon);
    }

    public function utc(): \DateTimeImmutable
    {
        return $this->localDateTime->setTimezone(new \DateTimeZone('UTC'));
    }
}
