<?php

declare(strict_types=1);

namespace App\Domain;

final class Activation
{
    public function __construct(
        public readonly string $body,
        public readonly float $longitude,
        public readonly int $gate,
        public readonly int $line,
        public readonly int $color,
        public readonly int $tone,
        public readonly int $base,
    ) {
    }

    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'longitude' => round($this->longitude, 6),
            'gate' => $this->gate,
            'line' => $this->line,
            'color' => $this->color,
            'tone' => $this->tone,
            'base' => $this->base,
        ];
    }
}
