<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminUserController;

// ─────────────────────────────────────────────────────────────────────────────
// Root → redirect
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ─────────────────────────────────────────────────────────────────────────────
// Auth (tamu saja)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ─────────────────────────────────────────────────────────────────────────────
// Route terautentikasi
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.status'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── CRUD User (Admin - Kelola Akun + Profil) ─────────────────────────────
    // Khusus untuk laboran mengelola user dengan tabel profil terpisah
    Route::middleware('role:laboran')->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/',              [AdminUserController::class, 'index'])->name('index');
        Route::get('/create',        [AdminUserController::class, 'create'])->name('create');
        Route::post('/',             [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}',        [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit',   [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}',        [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}',     [AdminUserController::class, 'destroy'])->name('destroy');
    });

    // ── CRUD User (Lama - untuk backward compatibility) ──────────────────────
    // Semua route users dalam SATU prefix group agar urutan terjaga.
    // KRITIS: route statis (/users/create) HARUS sebelum route dinamis (/users/{user}).
    // Pembatasan hak akses per-action ditangani di dalam UserController.
    Route::middleware('role:laboran,staf_prodi')->prefix('users')->name('users.')->group(function () {

        // Statis — harus PERTAMA sebelum {user}
        Route::get('/',       [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');  // <-- sebelum {user}
        Route::post('/',      [UserController::class, 'store'])->name('store');

        // Dinamis — setelah semua yang statis
        Route::get('/{user}',       [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit',  [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',       [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',    [UserController::class, 'destroy'])->name('destroy');
    });
});
