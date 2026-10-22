<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Services\AuthService;
use App\Services\DashboardMahasiswaService;
use App\Support\Role;

/**
 * Dashboard (SRS D.16) dengan data nyata modul Peminjaman:
 * - Laboran/Admin: jumlah pengajuan menunggu persetujuan + peminjaman berlangsung.
 * - Mahasiswa: delegasi penuh ke DashboardMahasiswaService (ringkasan, jadwal kuliah, pembaruan, dsb.).
 * - Dosen/Staff: pengajuan disetujui/selesai/total, riwayat terbaru, peminjaman terdekat, tombol Ajukan.
 */
class DashboardController extends Controller
{
    public function index(DashboardMahasiswaService $mahasiswa)
    {
        $me = AuthService::user();
        // Isi dashboard berbeda per role; cabang di bawah memilih view yang sesuai.
        $isAdmin = $me['role'] === Role::LABORAN;

        if ($isAdmin) {
            return view('dashboard.index', [
                'me' => $me,
                'isAdmin' => true,
                'menunggu' => Peminjaman::menungguTindakan($me['email'], $me['role'])->count(),
                'berlangsung' => Peminjaman::where('status', 'disetujui')->count(),
            ]);
        }

        // Mahasiswa: semua data disusun DashboardMahasiswaService.
        if ($me['role'] === Role::MAHASISWA) {
            return view('dashboard.mahasiswa', array_merge(['me' => $me, 'isAdmin' => false], $mahasiswa->untuk($me)));
        }

        // Staf Prodi punya dashboard akademik tersendiri (jumlah data + jadwal terdekat).
        if ($me['role'] === Role::STAFF) {
            return app(DashboardControllerStaff::class)->index();
        }

<<<<<<< HEAD
=======
        // Query dasar: semua peminjaman milik pengguna ini (dipakai berulang untuk hitungan).
>>>>>>> c4ec5d9 (refactor: rapikan SIARKA dan perbaiki akses serta peminjaman)
        $dasar = fn () => Peminjaman::where('email_peminjam', $me['email']);
        $terbaru = $dasar()->with(Peminjaman::withProfil())
            ->orderByDesc('tanggal_pengajuan')->limit(5)->get();

        return view('dashboard.index', [
            'me' => $me,
            'isAdmin' => false,
            'role' => $me['role'],
            'disetujui' => $dasar()->where('status', 'disetujui')->count(),
            'selesai' => $dasar()->where('status', 'selesai')->count(),
            'total' => $dasar()->count(),
            'terbaru' => $terbaru,
            'terdekat' => $dasar()->where('status', 'disetujui')
                ->where('tanggal_peminjaman', '>=', now())
                ->orderBy('tanggal_peminjaman')->first(),
            // Hanya Dosen yang punya antrean persetujuan; Staf Prodi tidak sampai ke sini.
            'menunggu' => $me['role'] === Role::DOSEN
                ? Peminjaman::menungguTindakan($me['email'], $me['role'])->count()
                : 0,
        ]);
    }
}
