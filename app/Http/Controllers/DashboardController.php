<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Support\Role;

/**
 * Dashboard mengikuti SRS D.16 apa adanya:
 * - Admin/Laboran: HANYA jumlah Pengajuan menunggu persetujuan + jumlah Peminjaman berlangsung.
 * - Mahasiswa/Dosen: Pengajuan disetujui/selesai/terbaru, total riwayat, Peminjaman terdekat, tombol Ajukan.
 * Modul Peminjaman belum dibangun (di luar cakupan revisi ini) -> seluruh angka di atas
 * ditampilkan sebagai status "belum tersedia", bukan dikarang menjadi 0.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $me = AuthService::user();

        // Staf Prodi punya dashboard akademik tersendiri (jumlah data + jadwal terdekat).
        if ($me['role'] === Role::STAFF) {
            return app(DashboardControllerStaff::class)->index();
        }

        return view('dashboard.index', [
            'me' => $me,
            'isAdmin' => $me['role'] === Role::LABORAN,
        ]);
    }
}
