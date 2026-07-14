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
        <pre><?= htmlspecialchars(json_encode($chart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>

        <p><a class="button-link" href="/">Novo cálculo</a></p>
    </section>
</main>
</body>
</html>
