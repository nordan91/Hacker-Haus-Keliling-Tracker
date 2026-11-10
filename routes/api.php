<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Huling Tracker
|--------------------------------------------------------------------------
|
| Auth: Laravel Sanctum (token-based)
| Middleware alias 'role' => \App\Http\Middleware\RoleMiddleware::class
| Register this alias in bootstrap/app.php or app/Http/Kernel.php
|
*/

// ─── Public (No Auth) ────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ─── Authenticated Routes ────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // ─── Products ────────────────────────────────────────────────────
    // All authenticated users can list/view products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Admin-only CRUD
    Route::middleware('role:admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    // ─── Locations ───────────────────────────────────────────────────
    // All authenticated users can list/view locations
    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{location}', [LocationController::class, 'show']);

    // Admin-only CRUD
    Route::middleware('role:admin')->group(function () {
        Route::post('/locations', [LocationController::class, 'store']);
        Route::put('/locations/{location}', [LocationController::class, 'update']);
        Route::delete('/locations/{location}', [LocationController::class, 'destroy']);
    });

    // ─── Shifts (Driver) ────────────────────────────────────────────
    Route::middleware('role:driver')->prefix('shifts')->group(function () {
        Route::post('/start', [ShiftController::class, 'start']);
        Route::get('/current', [ShiftController::class, 'current']);
        Route::get('/history', [ShiftController::class, 'history']);
        Route::post('/{shift}/inventories', [ShiftController::class, 'storeInventory']);
        Route::get('/{shift}/inventories', [ShiftController::class, 'getInventory']);
        Route::post('/{shift}/close', [ShiftController::class, 'close']);
        Route::get('/{shift}/recap', [ShiftController::class, 'recap']);
    });

    // Allow admin to also view shift recap and inventories
    Route::middleware('role:admin')->prefix('shifts')->group(function () {
        Route::get('/{shift}/inventories', [ShiftController::class, 'getInventory']);
        Route::get('/{shift}/recap', [ShiftController::class, 'recap']);
    });

    // ─── Transactions (Driver) ──────────────────────────────────────
    Route::middleware('role:driver')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/pending-qris', [TransactionController::class, 'pendingQris']);
        Route::patch('/transactions/{transaction}/validate', [TransactionController::class, 'validateQris']);
        Route::patch('/transactions/{transaction}/reject', [TransactionController::class, 'rejectQris']);
    });

    // Both admin and driver can list and view transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
});

