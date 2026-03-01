<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // L5-Swagger v2.1 is not compatible with Laravel 12 - temporarily disabled
    // ->withProviders([
    //     \Darkaonline\L5Swagger\L5SwaggerServiceProvider::class,
    // ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // Exclude auth routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'auth/set-session',
            'auth/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
