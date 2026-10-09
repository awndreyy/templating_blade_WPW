<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ApiAuthController;

// Route login: tidak perlu token
Route::post('/login', [ApiAuthController::class, 'login']);

// Route yang membutuhkan autentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
});

Route::apiResource('products', ProductController::class);
