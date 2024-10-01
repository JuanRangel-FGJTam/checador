<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\AuthorizedMenu::class
        ]);

        $middleware->alias([
            'authorized.menu' => \App\Http\Middleware\AuthorizedMenu::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
