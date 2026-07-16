<?php

declare(strict_types=1);

namespace App\Services;

final class IncarnationCrossCalculator
{
    public function calculate(
        int $personalitySunGate,
        int $personalityEarthGate,
        int $designSunGate,
        int $designEarthGate
    ): array {
        $gates = [
            $personalitySunGate,
            $personalityEarthGate,
            $designSunGate,
            $designEarthGate,
        ];

        foreach ($gates as $gate) {
            $this->validateGate($gate);
        }

        return [
            'gates' => $gates,
            'quarter' => null,
            'angle' => null,
            'name' => null,
            'status' => 'unresolved',
        ];
    }

    private function validateGate(int $gate): void
    {
        if ($gate < 1 || $gate > 64) {
            throw new \InvalidArgumentException("Gate inválido: {$gate}. Esperado valor entre 1 e 64.");
        }
    }
}
