<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlatBahanController;

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
