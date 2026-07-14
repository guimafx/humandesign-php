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
        <p class="eyebrow">HumanDesign PHP</p>
        <h1>Calculadora Human Design</h1>
        <p class="muted">
            Base MVC inicial. Em modo demo, o resultado serve somente para testar a aplicação.
        </p>

        <form method="post" action="/chart/calculate" class="form-grid">
            <label>
                Nome
                <input name="name" required value="Teste">
            </label>

            <label>
                Data de nascimento
                <input type="date" name="date" required value="1987-05-14">
            </label>

            <label>
                Hora de nascimento
                <input type="time" name="time" required value="13:30">
            </label>

            <label>
                Timezone IANA
                <input name="timezone" required value="America/Sao_Paulo">
            </label>

            <label>
                Latitude
                <input type="number" step="any" name="latitude" value="-27.5949">
            </label>

            <label>
                Longitude
                <input type="number" step="any" name="longitude" value="-48.5482">
            </label>

            <div class="full">
                <button type="submit">Calcular</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
