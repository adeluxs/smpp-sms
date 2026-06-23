<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = $app->make(Illuminate\Http\Request::class);
$response = $app->make(Illuminate\Routing\Router::class)->dispatch($request);

$response->send();