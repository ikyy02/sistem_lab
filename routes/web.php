<?php

use App\Http\Controllers\AlatBahanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataAkademikController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelolaKatalogController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengaturanOperasionalController;
use App\Http\Controllers\ProfilController;
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

    // Katalog: dapat dilihat semua role (hanya-baca untuk selain Laboran/Admin)
    Route::get('katalog', [AlatBahanController::class, 'index'])->name('katalog');

    // Profil Saya: semua role yang login
    Route::get('profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('profil/password', [ProfilController::class, 'password'])->name('profil.password');

    // Khusus Staf Prodi
    Route::middleware('silab.role:' . Role::STAFF)->group(function () {

        // Data Akademik: Mata Kuliah, Kelas, Semester, dan Dosen (satu menu, empat tab)
        $tabAkademik = ['mata-kuliah', 'kelas', 'dosen'];
        Route::get('data-akademik', [DataAkademikController::class, 'index'])->name('data-akademik.index');
        Route::post('data-akademik/{tab}', [DataAkademikController::class, 'store'])
            ->whereIn('tab', $tabAkademik)->name('data-akademik.store');
        Route::put('data-akademik/{tab}/{id}', [DataAkademikController::class, 'update'])
            ->whereIn('tab', $tabAkademik)->where('id', '[A-Za-z0-9._\-]+')->name('data-akademik.update');
        Route::delete('data-akademik/{tab}/{id}', [DataAkademikController::class, 'destroy'])
            ->whereIn('tab', $tabAkademik)->where('id', '[A-Za-z0-9._\-]+')->name('data-akademik.destroy');

        // Kelola Jadwal Perkuliahan (cek bentrok ruangan/hari/jam lewat JadwalService)
        Route::get('jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal/create', [JadwalController::class, 'create'])->name('jadwal.create');
        Route::post('jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
        Route::get('jadwal/{jadwal}', [JadwalController::class, 'show'])->whereNumber('jadwal')->name('jadwal.show');
        Route::get('jadwal/{jadwal}/edit', [JadwalController::class, 'edit'])->whereNumber('jadwal')->name('jadwal.edit');
        Route::put('jadwal/{jadwal}', [JadwalController::class, 'update'])->whereNumber('jadwal')->name('jadwal.update');
        Route::delete('jadwal/{jadwal}', [JadwalController::class, 'destroy'])->whereNumber('jadwal')->name('jadwal.destroy');
    });

    // Transaksi Peminjaman: pengajuan & riwayat (hanya Mahasiswa dan Dosen yang dapat mengajukan)
    Route::middleware('silab.role:' . Role::MAHASISWA . ',' . Role::DOSEN)->group(function () {
        Route::get('peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::post('peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::get('peminjaman/riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');
    });

    // Detail transaksi: pemilik pengajuan, Dosen, dan Laboran/Admin (dicek ulang di controller)
    Route::get('peminjaman/{id}', [PeminjamanController::class, 'show'])->whereNumber('id')->name('peminjaman.show');

    // Transaksi Peminjaman: persetujuan pengajuan & pengembalian (Dosen dan Laboran/Admin)
    Route::middleware('silab.role:' . Role::DOSEN . ',' . Role::LABORAN)->group(function () {
        Route::get('pengajuan-peminjaman', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::post('pengajuan-peminjaman/{id}/setujui', [PengajuanController::class, 'setujui'])->whereNumber('id')->name('pengajuan.setujui');
        Route::post('pengajuan-peminjaman/{id}/tolak', [PengajuanController::class, 'tolak'])->whereNumber('id')->name('pengajuan.tolak');
        Route::get('pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
        Route::post('pengembalian/{id}', [PengembalianController::class, 'store'])->whereNumber('id')->name('pengembalian.store');
    });

    // Khusus Laboran/Admin
    Route::middleware('silab.role:' . Role::LABORAN)->group(function () {

        // Kelola Katalog (Alat, Bahan, Ruangan — satu menu, 3 tab terpisah)
        $kat = ['kategori' => array_keys(KelolaKatalogController::KATEGORI)];
        Route::get('kelola-katalog', [KelolaKatalogController::class, 'index'])->name('kelola-katalog.index');
        Route::post('kelola-katalog/{kategori}', [KelolaKatalogController::class, 'store'])->whereIn('kategori', $kat['kategori'])->name('kelola-katalog.store');
        Route::put('kelola-katalog/{kategori}/{id}', [KelolaKatalogController::class, 'update'])->whereIn('kategori', $kat['kategori'])->whereNumber('id')->name('kelola-katalog.update');
        Route::post('kelola-katalog/{kategori}/{id}/stok', [KelolaKatalogController::class, 'stok'])->whereIn('kategori', ['alat', 'bahan'])->whereNumber('id')->name('kelola-katalog.stok');
        Route::delete('kelola-katalog/{kategori}/{id}', [KelolaKatalogController::class, 'destroy'])->whereIn('kategori', $kat['kategori'])->whereNumber('id')->name('kelola-katalog.destroy');
        Route::get('kelola-katalog/{kategori}/template', [KelolaKatalogController::class, 'template'])->whereIn('kategori', $kat['kategori'])->name('kelola-katalog.template');
        Route::post('kelola-katalog/{kategori}/import', [KelolaKatalogController::class, 'import'])->whereIn('kategori', $kat['kategori'])->name('kelola-katalog.import');

        // Pengaturan Operasional: jam dan hari operasional
        Route::get('pengaturan-operasional', [PengaturanOperasionalController::class, 'index'])->name('pengaturan-operasional.index');
        Route::put('pengaturan-operasional', [PengaturanOperasionalController::class, 'update'])->name('pengaturan-operasional.update');

        // Kelola Data Master: Satuan dan Kelas
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
