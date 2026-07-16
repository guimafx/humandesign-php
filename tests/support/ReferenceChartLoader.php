<?php

declare(strict_types=1);

final class ReferenceChartLoader
{
    private const STATUSES = ['active', 'pending'];
    private const REQUIRED_FIELDS = ['status', 'id', 'label', 'birth', 'expected', 'source', 'privacy'];

    private readonly string $directory;

    public function __construct(?string $directory = null)
    {
        $directory ??= dirname(__DIR__) . '/reference';
        $realDirectory = realpath($directory);

        if ($realDirectory === false || !is_dir($realDirectory)) {
            throw new RuntimeException("Diretório de fixtures inexistente: {$directory}");
        }

        $this->directory = rtrim($realDirectory, DIRECTORY_SEPARATOR);
    }

    /** @return array{active: list<array<string, mixed>>, pending: list<array<string, mixed>>} */
    public function loadAll(): array
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            throw new RuntimeException("Não foi possível listar fixtures em {$this->directory}");
        }

        sort($files, SORT_STRING);
        $collections = ['active' => [], 'pending' => []];
        $ids = [];

        foreach ($files as $file) {
            $fixture = $this->read($file);
            $this->validate($fixture, basename($file));

            $id = $fixture['id'];
            if (isset($ids[$id])) {
                throw new RuntimeException("Fixture com id duplicado: {$id}.");
            }

            $ids[$id] = true;
            $collections[$fixture['status']][] = $fixture;
        }

        return $collections;
    }

    /** @return array<string, mixed> */
    private function read(string $file): array
    {
        $realFile = realpath($file);
        if ($realFile === false
            || dirname($realFile) !== $this->directory
            || pathinfo($realFile, PATHINFO_EXTENSION) !== 'php') {
            throw new RuntimeException('Fixture fora do diretório de referências ou com extensão inválida.');
        }

        $fixture = require $realFile;
        if (!is_array($fixture)) {
            throw new RuntimeException('Fixture deve retornar array: ' . basename($realFile) . '.');
        }

        return $fixture;
    }

    /** @param array<string, mixed> $fixture */
    private function validate(array $fixture, string $name): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $fixture)) {
                throw new RuntimeException("Fixture {$name}: campo obrigatório ausente: {$field}.");
            }
        }

        $status = $fixture['status'];
        if (!is_string($status) || !in_array($status, self::STATUSES, true)) {
            throw new RuntimeException(sprintf(
                'Fixture %s possui status inválido: %s.',
                $name,
                is_scalar($status) ? (string) $status : get_debug_type($status)
            ));
        }

        foreach (['id', 'label'] as $field) {
            if (!is_string($fixture[$field]) || trim($fixture[$field]) === '') {
                throw new RuntimeException("Fixture {$name}: {$field} deve ser string não vazia.");
            }
        }

        if ($status === 'active') {
            $this->validateActive($fixture, $name);
            return;
        }

        $this->validatePending($fixture, $name);
    }

    /** @param array<string, mixed> $fixture */
    private function validateActive(array $fixture, string $name): void
    {
        $this->requireKeys($fixture['birth'], ['date', 'time', 'timezone', 'latitude', 'longitude'], $name, 'birth');
        $this->requireKeys($fixture['source'], ['provider', 'reference', 'checked_at'], $name, 'source');
        $this->requireKeys($fixture['privacy'], ['consent', 'anonymized'], $name, 'privacy');

        if (!is_array($fixture['expected']) || $fixture['expected'] === []) {
            throw new RuntimeException("Fixture {$name}: expected deve conter ao menos um resultado validado.");
        }
        if (!is_string($fixture['birth']['date'])
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fixture['birth']['date']) !== 1
            || !is_string($fixture['birth']['time'])
            || preg_match('/^\d{2}:\d{2}$/', $fixture['birth']['time']) !== 1) {
            throw new RuntimeException("Fixture {$name}: birth.date ou birth.time possui formato inválido.");
        }
        try {
            new DateTimeZone((string) $fixture['birth']['timezone']);
        } catch (Throwable) {
            throw new RuntimeException("Fixture {$name}: birth.timezone é inválido.");
        }
        foreach (['provider', 'reference'] as $field) {
            if (!is_string($fixture['source'][$field]) || trim($fixture['source'][$field]) === '') {
                throw new RuntimeException("Fixture {$name}: source.{$field} deve ser string não vazia.");
            }
        }
        if (!is_string($fixture['source']['checked_at'])
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fixture['source']['checked_at']) !== 1) {
            throw new RuntimeException("Fixture {$name}: source.checked_at deve usar YYYY-MM-DD.");
        }
        foreach (['consent', 'anonymized'] as $field) {
            if (!is_bool($fixture['privacy'][$field])) {
                throw new RuntimeException("Fixture {$name}: privacy.{$field} deve ser booleano.");
            }
        }
        if ($fixture['privacy']['consent'] !== true && $fixture['privacy']['anonymized'] !== true) {
            throw new RuntimeException("Fixture {$name}: consentimento ou anonimização é obrigatório.");
        }
    }

    /** @param array<string, mixed> $fixture */
    private function validatePending(array $fixture, string $name): void
    {
        if (!is_array($fixture['birth']) || !is_array($fixture['expected'])) {
            throw new RuntimeException("Fixture {$name}: birth e expected devem ser arrays, mesmo quando pendentes.");
        }
        $this->requireKeys($fixture['source'], ['provider', 'reference', 'checked_at'], $name, 'source');
        $this->requireKeys($fixture['privacy'], ['consent', 'anonymized'], $name, 'privacy');
        if (!is_string($fixture['source']['reference']) || trim($fixture['source']['reference']) === '') {
            throw new RuntimeException("Fixture {$name}: source.reference deve indicar a referência necessária.");
        }
        foreach (['consent', 'anonymized'] as $field) {
            if (!is_bool($fixture['privacy'][$field])) {
                throw new RuntimeException("Fixture {$name}: privacy.{$field} deve ser booleano.");
            }
        }
    }

    private function requireKeys(mixed $value, array $keys, string $file, string $path): void
    {
        if (!is_array($value)) {
            throw new RuntimeException("Fixture {$file}: {$path} deve ser array.");
        }
        foreach ($keys as $key) {
            if (!array_key_exists($key, $value)) {
                throw new RuntimeException("Fixture {$file}: campo obrigatório ausente: {$path}.{$key}.");
            }
        }
    }
}
