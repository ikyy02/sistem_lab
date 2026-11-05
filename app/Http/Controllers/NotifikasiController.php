<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Services\AuthService;

/**
 * Notifikasi akun yang sedang login (lonceng topbar dan halaman daftar).
 * Semua aksi selalu dibatasi ke notifikasi milik akun pemilik sesi.
 */
class NotifikasiController extends Controller
{
    public const PER_PAGE = 25;

    public function index()
    {
        $akun = $this->akun();
        $data = $akun->notifications()->paginate(self::PER_PAGE)->withQueryString();

        return view('notifikasi.index', [
            'data' => $data,
            'unread' => $akun->unreadNotifications()->count(),
        ]);
    }

    public function baca(string $id)
    {
        $notifikasi = $this->akun()->notifications()->whereKey($id)->firstOrFail();
        $notifikasi->markAsRead();

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function bacaSemua()
    {
        $this->akun()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function akun(): Akun
    {
        $akun = Akun::find(AuthService::user()['email'] ?? '');
        abort_if(! $akun, 403);

        return $akun;
    }
}
