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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'monthly.tasks' => \App\Http\Middleware\CheckMonthlyTasks::class,
            'ensure.activated' => \App\Http\Middleware\EnsureAppActivated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $e, $request) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                // Invalidate session and redirect to login to avoid "Page Expired" loops
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }
            return $response;
        });
    })->create();
