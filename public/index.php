<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Env;
use App\Core\Router;
use App\Services\DemoEphemerisProvider;
use App\Services\HumanDesignCalculator;
use App\Services\StrictEphemerisProvider;
use App\Services\SwissEphemerisProvider;

require dirname(__DIR__) . '/app/Core/Autoloader.php';

Env::load(dirname(__DIR__) . '/.env');

date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Sao_Paulo'));

$router = new Router();

$driver = Env::get('EPHEMERIS_DRIVER', 'strict');

$ephemeris = match ($driver) {
    'demo' => new DemoEphemerisProvider(),
    'swiss' => new SwissEphemerisProvider(
        (string) Env::get('SWETEST_BIN', '/usr/local/bin/swetest'),
        (string) Env::get(
            'SWISSEPH_EPHE_PATH',
            '/usr/local/share/swisseph/ephe'
        )
    ),
    default => new StrictEphemerisProvider(),
};

$calculator = new HumanDesignCalculator($ephemeris);

$app = new Application(
    $router,
    dirname(__DIR__),
    [
        HumanDesignCalculator::class => $calculator,
    ]
);

require dirname(__DIR__) . '/routes/web.php';

$app->run();
