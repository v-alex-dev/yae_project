<?php

use App\Http\Middleware\ResponseJson;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(remove: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->api(prepend: [
            ResponseJson::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        Authenticate    ::redirectUsing(fn () => null);

        // Render a clean 401 JSON body for every AuthenticationException.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // is('api/*') returns true for any route starting with /api/
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed.',
                    // errors() returns an array: [ 'field' => ['error msg', ...] ]
                    'errors'  => $e->errors(),
                ], 422);
            }
        });
    })->create();
