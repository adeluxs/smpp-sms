<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Log;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure()
    ->withRouting(
        config: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web();
        $middleware->api();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report();
    })
    ->withLog(function (Log $log) {
        $log->errors();
    })
    ->create();