<?php

use App\Controllers\HomeController;
use App\Controllers\ChartController;

/** @var \App\Core\Application $app */

$app->get('/', [HomeController::class, 'index']);
$app->post('/chart/calculate', [ChartController::class, 'calculate']);
$app->get('/health', [HomeController::class, 'health']);
