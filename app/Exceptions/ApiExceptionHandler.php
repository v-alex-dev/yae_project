<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Centralizes the conversion of any exception into a JSON response
 * for our API routes (everything under /api/*).
 *
 * Keeping this logic in a dedicated class instead of an inline closure
 * in bootstrap/app.php makes it easier to read, test, and extend later
 * (for example, when a future client app needs a specific error format).
 */
class ApiExceptionHandler
{
    /**
     * Try to turn the given exception into a JSON response.
     *
     * Returns null for non-API requests, so Laravel falls back
     * to its default (HTML) rendering.
     */
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        // Only handle requests targeting our API.
        if (! $request->is('api/*')) {
            return null;
        }

        // match(true) acts like a series of "if" checked from top to bottom,
        // and returns the value of the first matching branch.
        return match (true) {

            $e instanceof ValidationException => response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422),

            // Covers 404 (route not found), 405, etc.
            $e instanceof HttpExceptionInterface => self::error(
                $e->getMessage() ?: 'HTTP error.',
                $e->getStatusCode()
            ),

            // Fallback for any unexpected error (500).
            default => self::error(
                config('app.debug') ? $e->getMessage() : 'Server Error.',
                500
            ),
        };
    }

    /**
     * Small helper to build a consistent JSON error response shape.
     */
    private static function error(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
