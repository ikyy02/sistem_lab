<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Services\AuthService;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;

/**
 * Pengajuan Peminjaman (Dosen & Laboran/Admin): antrean pengajuan beserta persetujuan/penolakan.
 * Aturan bisnis (stok, konflik ruangan, status) dikerjakan PeminjamanService.
 */
class PengajuanController extends Controller
{
    public const PER_PAGE = [10, 25, 50, 100];

    public const FILTER = ['menunggu', 'disetujui', 'ditolak', 'selesai', 'dibatalkan', 'semua'];

    // Antrean pengajuan sesuai kewenangan role (lihat Peminjaman::scopeDapatDiprosesOleh). Default tab 'menunggu'.
    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu');
        $status = in_array($status, self::FILTER, true) ? $status : 'menunggu';
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $me = AuthService::user();
        $query = Peminjaman::query()->with(Peminjaman::withProfil())
            ->dapatDiprosesOleh($me['email'], $me['role'])
            ->when($status === 'menunggu', fn ($q) => $q->menungguTindakan($me['email'], $me['role']))
            ->when(! in_array($status, ['semua', 'menunggu'], true), fn ($q) => $q->where('status', $status));
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';
            $query->where(function ($g) use ($like) {
                $g->whereRaw("email_peminjam LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("keterangan LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('mahasiswaPeminjam', fn ($q) => $q
                        ->whereRaw("nama LIKE ? ESCAPE '!'", [$like])
                        ->orWhereRaw("nim LIKE ? ESCAPE '!'", [$like]))
                    ->orWhereHas('dosenPeminjam', fn ($q) => $q->whereRaw("nama LIKE ? ESCAPE '!'", [$like]));
            });
        }

        $data = $query->orderByDesc('tanggal_pengajuan')->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage() && $data->lastPage() > 0) {
            return redirect()->route('pengajuan.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('pengajuan.index', [
            'data' => $data,
            'status' => $status,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'labels' => Peminjaman::LABELS,
            'jumlahMenunggu' => Peminjaman::menungguTindakan($me['email'], $me['role'])->count(),
        ]);
    }

    // Setujui: otorisasi dulu, lalu service. Dosen = teruskan ke Laboran; Laboran = final dan stok berkurang.
    public function setujui(int $id, PeminjamanService $service)
    {
        $this->otorisasi($id);
        $p = $service->setujui($id, AuthService::user()['email']);

        return redirect()->back()->with('success', $p->status === 'menunggu'
            ? 'Pengajuan disetujui dan diteruskan ke Laboran untuk keputusan akhir.'
            : 'Pengajuan peminjaman disetujui dan stok barang telah diperbarui.');
    }

    // Tolak wajib menyertakan alasan.
    public function tolak(Request $request, int $id, PeminjamanService $service)
    {
        $this->otorisasi($id);
        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ], [
            'required' => 'Alasan penolakan wajib diisi.',
            'max' => 'Alasan penolakan maksimal :max karakter.',
        ], ['alasan' => 'Alasan penolakan']);

        $service->tolak($id, AuthService::user()['email'], $data['alasan']);

        return redirect()->back()->with('success', 'Pengajuan peminjaman ditolak. Stok barang tidak berubah.');
    }

    /** Pengajuan di luar kewenangan role ditolak 403 sebelum diproses (bukan hanya disembunyikan dari daftar). */
    // Cek hak akses di backend sebelum memproses, bukan hanya menyembunyikan tombol.
    private function otorisasi(int $id): void
    {
        $me = AuthService::user();
        abort_unless(Peminjaman::whereKey($id)->dapatDiprosesOleh($me['email'], $me['role'])->exists(), 403);
    }
}
