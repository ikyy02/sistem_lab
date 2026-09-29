<?php

use App\Http\Controllers\AlatBahanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\UserManagementController;
use App\Services\AuthService;
use App\Support\Role;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Role: mahasiswa | dosen | staff | laboran (Laboran/Admin)
*/

// Login / logout (tanpa registrasi: akun dikelola Laboran/Admin)
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('silab.auth')->group(function () {

    // Halaman awal: arahkan sesuai role (dashboard dikembangkan pada tahap berikutnya)
    Route::get('/', fn () => redirect(Role::home(AuthService::user()['role'])));

    // Katalog inventaris: dapat dilihat semua role (hanya-baca untuk selain Laboran/Admin)
    Route::get('katalog', [AlatBahanController::class, 'index'])->name('katalog');

    // Khusus Laboran/Admin
    Route::middleware('silab.role:' . Role::LABORAN)->group(function () {

        // Kelola Inventaris (Alat, Bahan, Ruangan — satu menu, 3 kategori terpisah)
        Route::get('inventaris', [InventarisController::class, 'index'])->name('inventaris.index');
        Route::post('inventaris/{kategori}', [InventarisController::class, 'store'])
            ->whereIn('kategori', array_keys(InventarisController::KATEGORI))->name('inventaris.store');
        Route::put('inventaris/{kategori}/{id}', [InventarisController::class, 'update'])
            ->whereIn('kategori', array_keys(InventarisController::KATEGORI))->whereNumber('id')->name('inventaris.update');
        Route::delete('inventaris/{kategori}/{id}', [InventarisController::class, 'destroy'])
            ->whereIn('kategori', array_keys(InventarisController::KATEGORI))->whereNumber('id')->name('inventaris.destroy');
        Route::get('inventaris/{kategori}/template', [InventarisController::class, 'template'])
            ->whereIn('kategori', array_keys(InventarisController::KATEGORI))->name('inventaris.template');
        Route::post('inventaris/{kategori}/import', [InventarisController::class, 'import'])
            ->whereIn('kategori', array_keys(InventarisController::KATEGORI))->name('inventaris.import');

        // Satuan: CRUD lewat modal pada halaman Alat/Bahan (tanpa menu/tabel terpisah)
        Route::post('satuan', [SatuanController::class, 'store'])->name('satuan.store');
        Route::put('satuan/{id}', [SatuanController::class, 'update'])->whereNumber('id')->name('satuan.update');
        Route::delete('satuan/{id}', [SatuanController::class, 'destroy'])->whereNumber('id')->name('satuan.destroy');

        // Kelola Data User (satu menu, 4 kategori)
        Route::get('kelola-user', [UserManagementController::class, 'index'])->name('kelola-user.index');
        Route::post('kelola-user/{kategori}', [UserManagementController::class, 'store'])
            ->whereIn('kategori', Role::ALL)->name('kelola-user.store');
        Route::put('kelola-user/{kategori}/{id}', [UserManagementController::class, 'update'])
            ->whereIn('kategori', Role::ALL)->whereNumber('id')->name('kelola-user.update');
        Route::delete('kelola-user/{kategori}/{id}', [UserManagementController::class, 'destroy'])
            ->whereIn('kategori', Role::ALL)->whereNumber('id')->name('kelola-user.destroy');

        // Import & template Excel mahasiswa (dipakai di kategori Mahasiswa)
        Route::get('mahasiswa/template', [MahasiswaController::class, 'template'])->name('mahasiswa.template');
        Route::post('mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
    });
});
