<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Services\AuthService;
use App\Support\Role;

/**
 * Dashboard (SRS D.16) dengan data nyata modul Peminjaman:
 * - Laboran/Admin: jumlah pengajuan menunggu persetujuan + peminjaman berlangsung.
 * - Mahasiswa/Dosen: pengajuan disetujui/selesai/total, riwayat terbaru, peminjaman terdekat, tombol Ajukan.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $me = AuthService::user();
        $isAdmin = $me['role'] === Role::LABORAN;

        if ($isAdmin) {
            return view('dashboard.index', [
                'me' => $me,
                'isAdmin' => true,
                'menunggu' => Peminjaman::where('status', 'menunggu')->count(),
                'berlangsung' => Peminjaman::where('status', 'disetujui')->count(),
                'tertutup' => Peminjaman::where('status', 'ditolak')->count(),
            ]);
        }

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
            'menunggu' => $me['role'] === Role::DOSEN
                ? Peminjaman::where('status', 'menunggu')->where('email_peminjam', '!=', $me['email'])->count()
                : 0,
        ]);
    }
}
