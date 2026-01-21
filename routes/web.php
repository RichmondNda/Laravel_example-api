<?php

use Illuminate\Support\Facades\Route;

// Block all web access - API only
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Not Found',
        'error' => 'This is an API-only application. Please use /api/* endpoints or visit /docs/api for documentation.'
    ], 404);
});
