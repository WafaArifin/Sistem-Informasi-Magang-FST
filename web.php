<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Halaman awal: arahkan ke login atau dashboard sesuai status login
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Login & logout
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Area setelah login
Route::middleware('auth')->group(function () {
    // Mengarahkan ke dashboard sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/mahasiswa/dashboard', [DashboardController::class, 'mahasiswa'])
        ->middleware(RoleMiddleware::class . ':Mahasiswa')->name('dashboard.mahasiswa');

    Route::get('/dosen/dashboard', [DashboardController::class, 'dosen'])
        ->middleware(RoleMiddleware::class . ':Dosen')->name('dashboard.dosen');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware(RoleMiddleware::class . ':Admin')->name('dashboard.admin');
});
