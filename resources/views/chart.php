<?php

declare(strict_types=1);

$jsonFormatted = json_encode(
    $chart,
    JSON_PRETTY_PRINT
    | JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_THROW_ON_ERROR
);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<main class="container">
    <section class="card">
        <p class="eyebrow">Resultado</p>
        <h1><?= htmlspecialchars($birth->name) ?></h1>

        <?php if (!$chart['metadata']['reliable']): ?>
            <div class="warning">
                <?= htmlspecialchars($chart['metadata']['warning']) ?>
            </div>
        <?php endif; ?>

        <dl class="summary">
            <dt>Nascimento local</dt>
            <dd><?= htmlspecialchars($chart['birth']['local']) ?></dd>
            <dt>UTC</dt>
            <dd><?= htmlspecialchars($chart['birth']['utc']) ?></dd>
            <dt>Provedor</dt>
            <dd><?= htmlspecialchars($chart['metadata']['ephemeris']) ?></dd>
        </dl>

        <h2>Dados estruturados</h2>
        <button
            type="button"
            id="copy-json-button"
            data-copy-target="chart-json"
            aria-live="polite"
        >Copiar JSON</button>
        <pre id="chart-json"><?= htmlspecialchars($jsonFormatted, ENT_QUOTES, 'UTF-8') ?></pre>

        <p><a class="button-link" href="/">Novo cálculo</a></p>
    </section>
</main>
<script>
    (() => {
        const button = document.getElementById('copy-json-button');
        const json = document.getElementById(button.dataset.copyTarget);
        const originalText = button.textContent;
        let restoreTimer;

        const fallbackCopy = (text) => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                if (!document.execCommand('copy')) {
                    throw new Error('Copy command failed');
                }
            } finally {
                textarea.remove();
                button.focus();
            }
        };

        button.addEventListener('click', async () => {
            clearTimeout(restoreTimer);

            try {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    await navigator.clipboard.writeText(json.textContent);
                } else {
                    fallbackCopy(json.textContent);
                }

                button.textContent = 'JSON copiado!';
            } catch (error) {
                button.textContent = 'Falha ao copiar';
            }

            restoreTimer = setTimeout(() => {
                button.textContent = originalText;
            }, 2000);
        });
    })();
</script>
</body>
</html>
