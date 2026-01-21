<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force all exceptions to return JSON (except documentation routes)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            // Allow documentation routes
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Not Found',
                'error' => $e->getMessage() ?: 'The requested resource could not be found',
            ], 404);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            // Allow documentation routes
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => 'The requested resource does not exist',
            ], 404);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // Allow documentation routes
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error' => $e->getMessage(),
            ], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            // Allow documentation routes
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            // Allow documentation routes
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Server Error',
                'error' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            // Allow documentation routes
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => app()->environment('local') ? $e->getMessage() : 'An unexpected error occurred',
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            ], $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500);
        });
    })->create();
