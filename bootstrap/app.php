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
        $middleware->alias([
            'admin'  => \App\Http\Middleware\AdminOnly::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => $request->user()?->isAdmin() ? '/dashboard' : '/projects');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
