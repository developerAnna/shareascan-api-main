<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'access-token' => \App\Http\Middleware\SetAccessTokenAsAuthorization::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
        ]);

        $middleware->priority([
            \App\Http\Middleware\SetAccessTokenAsAuthorization::class
        ]);
        
        // Apply CORS to all API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\CorsMiddleware::class,
        ]);
    })
    // ->withExceptions(function (Exceptions $exceptions) {
    //     $exceptions->render(function (AuthenticationException $e, Request $request) {
    //         if ($request->is('api/*')) {
    //             return response()->json([
    //                 'message' => $e->getMessage(),
    //             ], 401);
    //         }
    //     });
    // })->create();

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                // For API routes, return JSON
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }

            if ($request->is('admin/*')) {
                // For admin panel, redirect to custom login page
                return redirect()->guest(route('adminloginget'));
            }

            // Default fallback
            return redirect()->guest(route('login'));
        });
    })->create();
