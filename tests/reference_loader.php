<?php

declare(strict_types=1);

require __DIR__ . '/support/ReferenceChartLoader.php';

function loaderFixture(string $status, string $id): string
{
    return "<?php return " . var_export([
        'status' => $status,
        'id' => $id,
        'label' => "Fixture {$id}",
        'birth' => [
            'date' => '1982-03-27',
            'time' => '11:05',
            'timezone' => 'America/Sao_Paulo',
            'latitude' => null,
            'longitude' => null,
        ],
        'expected' => ['type' => 'generator'],
        'source' => [
            'provider' => 'Teste',
            'reference' => 'Teste independente',
            'checked_at' => '2026-07-16',
        ],
        'privacy' => ['consent' => true, 'anonymized' => false],
    ], true) . ";\n";
}

function removeLoaderDirectory(string $directory): void
{
    $files = glob($directory . '/*');
    if ($files !== false) {
        foreach ($files as $file) {
            if (is_file($file) || is_link($file)) {
                unlink($file);
            }
        }
    }
    rmdir($directory);
}

function expectLoaderRuntimeException(callable $operation, string $messagePart): void
{
    try {
        $operation();
    } catch (RuntimeException $exception) {
        if (!str_contains($exception->getMessage(), $messagePart)) {
            throw new RuntimeException(sprintf(
                'Mensagem inesperada; esperado trecho %s, obtido %s.',
                var_export($messagePart, true),
                var_export($exception->getMessage(), true)
            ));
        }
        return;
    }

    throw new RuntimeException("RuntimeException esperada não foi lançada: {$messagePart}.");
}

$real = (new ReferenceChartLoader(__DIR__ . '/reference'))->loadAll();
if (count($real['active']) !== 1 || $real['active'][0]['id'] !== 'generator-emotional-001') {
    throw new RuntimeException('Fixture active principal não foi carregada corretamente.');
}
if (count($real['pending']) !== 4) {
    throw new RuntimeException('Separação entre fixtures active e pending está incorreta.');
}

$temporaryDirectories = [];
try {
    $makeDirectory = static function () use (&$temporaryDirectories): string {
        $directory = sys_get_temp_dir() . '/hd-reference-loader-' . bin2hex(random_bytes(8));
        if (!mkdir($directory, 0700)) {
            throw new RuntimeException("Não foi possível criar diretório temporário: {$directory}");
        }
        $temporaryDirectories[] = $directory;
        return $directory;
    };

    $ordered = $makeDirectory();
    file_put_contents($ordered . '/z.php', loaderFixture('active', 'z-id'));
    file_put_contents($ordered . '/a.php', loaderFixture('active', 'a-id'));
    file_put_contents($ordered . '/m.php', loaderFixture('pending', 'm-id'));
    $orderedFixtures = (new ReferenceChartLoader($ordered))->loadAll();
    if (array_column($orderedFixtures['active'], 'id') !== ['a-id', 'z-id']
        || array_column($orderedFixtures['pending'], 'id') !== ['m-id']) {
        throw new RuntimeException('Ordenação alfabética estável ou separação de coleções falhou.');
    }

    $invalidStatus = $makeDirectory();
    file_put_contents($invalidStatus . '/invalid.php', loaderFixture('unknown', 'invalid'));
    expectLoaderRuntimeException(
        static fn () => (new ReferenceChartLoader($invalidStatus))->loadAll(),
        'status inválido'
    );

    $notArray = $makeDirectory();
    file_put_contents($notArray . '/invalid.php', "<?php return 'invalid';\n");
    expectLoaderRuntimeException(
        static fn () => (new ReferenceChartLoader($notArray))->loadAll(),
        'deve retornar array'
    );

    $duplicate = $makeDirectory();
    file_put_contents($duplicate . '/a.php', loaderFixture('active', 'duplicate'));
    file_put_contents($duplicate . '/b.php', loaderFixture('pending', 'duplicate'));
    expectLoaderRuntimeException(
        static fn () => (new ReferenceChartLoader($duplicate))->loadAll(),
        'id duplicado'
    );

    foreach (['birth', 'expected'] as $missing) {
        $directory = $makeDirectory();
        $fixture = require __DIR__ . '/reference/guilherme.php';
        unset($fixture[$missing]);
        file_put_contents($directory . '/invalid.php', "<?php return " . var_export($fixture, true) . ";\n");
        expectLoaderRuntimeException(
            static fn () => (new ReferenceChartLoader($directory))->loadAll(),
            "campo obrigatório ausente: {$missing}"
        );
    }
} finally {
    foreach (array_reverse($temporaryDirectories) as $directory) {
        removeLoaderDirectory($directory);
    }
}

echo "Reference Loader test OK\n";
