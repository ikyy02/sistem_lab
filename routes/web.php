<?php

use App\Http\Controllers\AlatBahanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MahasiswaController;
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

    // Halaman awal: dashboard ringkas sesuai role
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

        // Kelola Data Master: Satuan, Kelas, Prodi, Kategori, Jenis, Status, Kondisi (sumber dropdown form terkait)
        Route::get('master-data', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('master-data/{kategori}', [MasterDataController::class, 'store'])
            ->whereIn('kategori', array_keys(MasterDataController::KATEGORI))->name('master-data.store');
        Route::put('master-data/{kategori}/{id}', [MasterDataController::class, 'update'])
            ->whereIn('kategori', array_keys(MasterDataController::KATEGORI))->whereNumber('id')->name('master-data.update');
        Route::delete('master-data/{kategori}/{id}', [MasterDataController::class, 'destroy'])
            ->whereIn('kategori', array_keys(MasterDataController::KATEGORI))->whereNumber('id')->name('master-data.destroy');

        // Kelola Data User (satu menu, 4 kategori)
        Route::get('kelola-user', [UserManagementController::class, 'index'])->name('kelola-user.index');
        Route::get('kelola-user/{kategori}/template', [UserManagementController::class, 'template'])
            ->whereIn('kategori', [Role::DOSEN, Role::STAFF, Role::LABORAN])->name('kelola-user.template');
        Route::post('kelola-user/{kategori}/import', [UserManagementController::class, 'import'])
            ->whereIn('kategori', [Role::DOSEN, Role::STAFF, Role::LABORAN])->name('kelola-user.import');
        Route::post('kelola-user/{kategori}', [UserManagementController::class, 'store'])
            ->whereIn('kategori', Role::ALL)->name('kelola-user.store');
        Route::put('kelola-user/{kategori}/{key}', [UserManagementController::class, 'update'])
            ->whereIn('kategori', Role::ALL)->where('key', '[A-Za-z0-9._\-]+')->name('kelola-user.update');
        Route::delete('kelola-user/{kategori}/{key}', [UserManagementController::class, 'destroy'])
            ->whereIn('kategori', Role::ALL)->where('key', '[A-Za-z0-9._\-]+')->name('kelola-user.destroy');

        // Import & template Excel mahasiswa (dipakai di kategori Mahasiswa)
        Route::get('mahasiswa/template', [MahasiswaController::class, 'template'])->name('mahasiswa.template');
        Route::post('mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');
    });
});
