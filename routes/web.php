<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DriverController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Huling Tracker
|--------------------------------------------------------------------------
*/

// Root Redirect
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('driver.pos');
    }
    return redirect()->route('login');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Driver Portal (Mobile-First POS) ────────────────────────────────
Route::middleware(['auth', 'role:driver'])->prefix('driver')->as('driver.')->group(function () {
    Route::get('/', fn () => redirect()->route('driver.pos'));

    // Shift & Initial Inventory
    Route::get('/shift', [DriverController::class, 'shift'])->name('shift');
    Route::post('/shift', [DriverController::class, 'startShift'])->name('shift.start');
    Route::post('/shift/close', [DriverController::class, 'closeShift'])->name('shift.close');

    // POS & Selling
    Route::get('/pos', [DriverController::class, 'pos'])->name('pos');
    Route::post('/pos/checkout', [DriverController::class, 'checkout'])->name('pos.checkout');

    // QRIS Anti-Fraud Validation Center
    Route::get('/qris-pending', [DriverController::class, 'qrisPending'])->name('qris.pending');
    Route::patch('/qris/{transaction}/validate', [DriverController::class, 'validateQris'])->name('qris.validate');
    Route::patch('/qris/{transaction}/reject', [DriverController::class, 'rejectQris'])->name('qris.reject');

    // Riwayat Semua Transaksi (Lintas Shift & Tanggal)
    Route::get('/transactions', [DriverController::class, 'transactions'])->name('transactions');

    // End-of-Day Recap
    Route::get('/recap', [DriverController::class, 'recap'])->name('recap');
});

// ─── Admin Analytics Dashboard ───────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dead-stock', [AdminController::class, 'deadStock'])->name('dead-stock');
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
    Route::get('/shifts', [AdminController::class, 'shifts'])->name('shifts');
    Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    
    // Driver Management
    Route::get('/drivers', [AdminController::class, 'drivers'])->name('drivers');
    Route::post('/drivers', [AdminController::class, 'storeDriver'])->name('drivers.store');
    Route::put('/drivers/{driver}', [AdminController::class, 'updateDriver'])->name('drivers.update');
    Route::delete('/drivers/{driver}', [AdminController::class, 'destroyDriver'])->name('drivers.destroy');
    Route::patch('/drivers/{driver}/toggle-status', [AdminController::class, 'toggleDriverStatus'])->name('drivers.toggle-status');
});
