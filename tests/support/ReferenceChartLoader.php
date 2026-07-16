<?php

declare(strict_types=1);

final class ReferenceChartLoader
{
    private const REQUIRED_FIELDS = ['id', 'label', 'birth', 'expected', 'source', 'privacy'];

    /** @return list<array<string, mixed>> */
    public function loadAll(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException("Diretório de fixtures inexistente: {$directory}");
        }

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            throw new RuntimeException("Não foi possível listar fixtures em {$directory}");
        }

        sort($files, SORT_STRING);
        $fixtures = [];
        $ids = [];

        foreach ($files as $file) {
            $fixture = $this->read($file);
            $status = $fixture['status'] ?? 'active';

            if ($status === 'pending') {
                continue;
            }
            if ($status !== 'active') {
                throw new RuntimeException(sprintf(
                    'Fixture %s possui status inválido: %s.',
                    basename($file),
                    is_scalar($status) ? (string) $status : get_debug_type($status)
                ));
            }

            $this->validate($fixture, $file);
            if (isset($ids[$fixture['id']])) {
                throw new RuntimeException("Fixture com id duplicado: {$fixture['id']}.");
            }

            $ids[$fixture['id']] = true;
            $fixtures[] = $fixture;
        }

        return $fixtures;
    }

    /** @return array<string, mixed> */
    public function load(string $file): array
    {
        $fixture = $this->read($file);
        if (($fixture['status'] ?? 'active') !== 'active') {
            throw new RuntimeException('Fixture não está ativa: ' . basename($file) . '.');
        }

        $this->validate($fixture, $file);
        return $fixture;
    }

    /** @return array<string, mixed> */
    private function read(string $file): array
    {
        if (!is_file($file)) {
            throw new RuntimeException("Fixture inexistente: {$file}");
        }

        $fixture = require $file;
        if (!is_array($fixture)) {
            throw new RuntimeException('Fixture deve retornar array: ' . basename($file) . '.');
        }

        return $fixture;
    }

    /** @param array<string, mixed> $fixture */
    private function validate(array $fixture, string $file): void
    {
        $name = basename($file);
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $fixture)) {
                throw new RuntimeException("Fixture {$name}: campo obrigatório ausente: {$field}.");
            }
        }

        foreach (['id', 'label'] as $field) {
            if (!is_string($fixture[$field]) || trim($fixture[$field]) === '') {
                throw new RuntimeException("Fixture {$name}: {$field} deve ser string não vazia.");
            }
        }

        $this->requireKeys($fixture['birth'], ['date', 'time', 'timezone', 'latitude', 'longitude'], $name, 'birth');
        $this->requireKeys($fixture['source'], ['provider', 'reference', 'checked_at'], $name, 'source');
        $this->requireKeys($fixture['privacy'], ['consent', 'anonymized'], $name, 'privacy');

        if (!is_array($fixture['expected']) || $fixture['expected'] === []) {
            throw new RuntimeException("Fixture {$name}: expected deve conter ao menos um resultado validado.");
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fixture['birth']['date'])
            || !preg_match('/^\d{2}:\d{2}$/', (string) $fixture['birth']['time'])) {
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
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fixture['source']['checked_at'])) {
            throw new RuntimeException("Fixture {$name}: source.checked_at deve usar YYYY-MM-DD.");
        }
        foreach (['consent', 'anonymized'] as $field) {
            if (!is_bool($fixture['privacy'][$field])) {
                throw new RuntimeException("Fixture {$name}: privacy.{$field} deve ser booleano.");
            }
        }
        if ($fixture['privacy']['consent'] !== true) {
            throw new RuntimeException("Fixture {$name}: consentimento é obrigatório para fixture ativa.");
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
