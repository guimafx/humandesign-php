<?php

declare(strict_types=1);

namespace App\Services;

final class ProfileCalculator
{
    public function calculate(int $personalitySunLine, int $designSunLine): array
    {
        $this->validateLine($personalitySunLine);
        $this->validateLine($designSunLine);

        return [
            'value' => "{$personalitySunLine}/{$designSunLine}",
            'personality_line' => $personalitySunLine,
            'design_line' => $designSunLine,
        ];
    }

    private function validateLine(int $line): void
    {
        if ($line < 1 || $line > 6) {
            throw new \InvalidArgumentException("Linha inválida: {$line}. Esperado valor entre 1 e 6.");
        }
    }
}
