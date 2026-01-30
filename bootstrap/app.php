<?php

use App\Http\Middleware\TrackAdminActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load Invoice Module Routes
            if (file_exists(base_path('Modules/Invoice/routes/web.php'))) {
                Route::middleware('web')
                    ->group(base_path('Modules/Invoice/routes/web.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add SetLocale middleware to web group
        $middleware->web(append: [
            SetLocale::class,
            TrackAdminActivity::class
        ]);

        // Use custom CSRF middleware
        $middleware->validateCsrfTokens(except: [
            '/language/switch',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
