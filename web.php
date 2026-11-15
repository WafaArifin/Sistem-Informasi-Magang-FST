<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dosen\VerifikasiController;
use App\Http\Controllers\Mahasiswa\InformasiController;
use App\Http\Controllers\Mahasiswa\PendaftaranController;
use App\Http\Controllers\Mahasiswa\PengajuanController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dosen/dashboard', [DashboardController::class, 'dosen'])
        ->middleware(RoleMiddleware::class . ':Dosen')->name('dashboard.dosen');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware(RoleMiddleware::class . ':Admin')->name('dashboard.admin');

    // ---------------- DOSEN ----------------
    Route::middleware(RoleMiddleware::class . ':Dosen')->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{id}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{id}', [VerifikasiController::class, 'store'])->name('verifikasi.store');
    });

    // ---------------- MAHASISWA ----------------
    Route::middleware(RoleMiddleware::class . ':Mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'mahasiswa'])->name('dashboard');

        Route::get('/informasi', [InformasiController::class, 'index'])->name('informasi');

        Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran');
        Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/baru', [PengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('/pengajuan/{id}', [PengajuanController::class, 'show'])->name('pengajuan.show');
        Route::get('/pengajuan/{id}/revisi', [PengajuanController::class, 'edit'])->name('pengajuan.edit');
        Route::put('/pengajuan/{id}', [PengajuanController::class, 'update'])->name('pengajuan.update');
    });
});
?php>
