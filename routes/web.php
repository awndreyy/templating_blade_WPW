<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

// Halaman Landing Page
Route::get('/', function () {
    return view('frontend');
})->name('home');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Role Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/laporan/produk', [LaporanController::class, 'produk'])->name('laporan.produk');
    Route::resource('/products', ProductController::class);
});

// Role Kasir
Route::middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/kasir', function () {
        return redirect()->route('kasir.dashboard');
    });
    Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
    Route::post('/kasir/transaksi', [KasirController::class, 'storeTransaction'])->name('kasir.transaksi.store');
    Route::get('/kasir/print-test', [ReceiptController::class, 'print'])->name('kasir.print.test');
    Route::get('/kasir/midtrans-demo', [MidtransController::class, 'index'])->name('midtrans.demo');
    Route::post('/checkout', [MidtransController::class, 'checkout'])->name('midtrans.checkout');
    Route::get('/midtrans/status/{orderId}', [MidtransController::class, 'status'])->name('midtrans.status');
});
