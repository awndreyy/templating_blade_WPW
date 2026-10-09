<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Halaman Landing Page (Frontend)
Route::get('/', function () {
    return view('frontend');
})->name('home');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Role Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::resource('/admin/products', ProductController::class);
});

// Role Kasir
Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
    Route::post('/kasir/transaksi', [KasirController::class, 'storeTransaction'])->name('kasir.transaksi.store');
    Route::get('/kasir', function () {
        return redirect()->route('kasir.dashboard');
    });
});
