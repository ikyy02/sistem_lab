<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlatBahanController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama — redirect ke halaman alat-bahan
Route::get('/', function () {
    return redirect()->route('alat-bahan.index');
});

// Resource CRUD Alat & Bahan
// Menggunakan tabel alat_bahans yang sudah ada di database sistem_lab
Route::resource('alat-bahan', AlatBahanController::class);

// Halaman katalog inventaris — alias tampilan index yang sama dengan filter jenis
Route::get('katalog', [AlatBahanController::class, 'index'])->name('katalog');

// Import & template Excel Data Mahasiswa.
// PENTING: harus dideklarasikan SEBELUM Route::resource, karena GET mahasiswa/template
// akan tertangkap oleh rute mahasiswa/{mahasiswa} (show) bila dideklarasikan sesudahnya.
Route::get('mahasiswa/template', [MahasiswaController::class, 'template'])->name('mahasiswa.template');
Route::post('mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');

// Resource CRUD Data Mahasiswa
Route::resource('mahasiswa', MahasiswaController::class);

// Resource CRUD Data Dosen
Route::resource('dosen', DosenController::class);